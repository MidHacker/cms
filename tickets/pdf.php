<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

// Include TCPDF library
require_once('tcpdf/tcpdf.php');

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Order ID required');
}

$order_id = intval($_GET['id']);

// Get order details
$sql = "SELECT o.*, u.full_name as client_name, u.phone as client_phone 
        FROM orders o 
        LEFT JOIN users u ON o.client_id = u.id 
        WHERE o.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$order = mysqli_fetch_assoc($result)) {
    die('Order not found');
}

// Create PDF
class ShippingLabelPDF extends TCPDF {
    // Page header
    public function Header() {
        // Logo
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'Courier Management System', 0, 1, 'C');
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 5, 'Shipping Label / Bon de Livraison', 0, 1, 'C');
        $this->Line(10, 20, $this->getPageWidth()-10, 20);
    }
    
    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Create new PDF document
$pdf = new ShippingLabelPDF('P', 'mm', 'A6', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Courier Management System');
$pdf->SetAuthor('CMS');
$pdf->SetTitle('Shipping Label - ' . $order['tracking_code']);
$pdf->SetSubject('Shipping Label');
$pdf->SetKeywords('Shipping, Label, Courier');

// Remove default header/footer
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// Set margins
$pdf->SetMargins(5, 25, 5);
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 9);

// Company info
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 6, 'COURIER MANAGEMENT SYSTEM', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 4, 'Professional Courier Services', 0, 1, 'C');
$pdf->Ln(3);

// Divider
$pdf->Line(5, $pdf->GetY(), $pdf->getPageWidth()-5, $pdf->GetY());
$pdf->Ln(5);

// Tracking info
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'TRACKING CODE: ' . $order['tracking_code'], 0, 1, 'C');
$pdf->Ln(2);

// Barcode (using basic barcode)
$style = array(
    'position' => 'C',
    'align' => 'C',
    'stretch' => false,
    'fitwidth' => true,
    'cellfitalign' => '',
    'border' => false,
    'hpadding' => 'auto',
    'vpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => false,
    'text' => true,
    'font' => 'helvetica',
    'fontsize' => 8,
    'stretchtext' => 4
);
$pdf->write1DBarcode($order['tracking_code'], 'C128', '', '', '', 15, 0.4, $style, 'N');

// Sender info
$pdf->Ln(8);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 6, 'FROM:', 0, 1);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 4, 
    $order['sender_name'] . "\n" .
    'Phone: ' . $order['sender_phone'] . "\n" .
    $order['sender_address'], 
0, 'L');

$pdf->Ln(5);

// Receiver info
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 6, 'TO:', 0, 1);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 4, 
    $order['receiver_name'] . "\n" .
    'Phone: ' . $order['receiver_phone'] . "\n" .
    'City: ' . $order['receiver_city'] . "\n" .
    $order['receiver_address'], 
0, 'L');

$pdf->Ln(5);

// Package details
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(40, 6, 'Product:', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 6, $order['product_description'], 0, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(40, 6, 'Weight:', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 6, $order['weight'] . ' kg', 0, 1);

if ($order['dimensions']) {
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(40, 6, 'Dimensions:', 0, 0);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 6, $order['dimensions'], 0, 1);
}

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(40, 6, 'Parcel Type:', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 6, ucfirst(str_replace('_', ' ', $order['parcel_type'])), 0, 1);

if ($order['cod_amount'] > 0) {
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(40, 6, 'COD Amount:', 0, 0);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 6, number_format($order['cod_amount'], 2) . ' MAD', 0, 1);
}

$pdf->Ln(5);

// Footer info
$pdf->SetFont('helvetica', '', 7);
$pdf->MultiCell(0, 3, 
    "Generated on: " . date('Y-m-d H:i:s') . "\n" .
    "Hub: " . ($order['hub'] ?: 'Main Hub') . "\n" .
    "Status: " . $order['status'], 
0, 'C');

// Close and output PDF
$pdf->Output('shipping_label_' . $order['tracking_code'] . '.pdf', 'I');

// Log ticket generation
$log_sql = "INSERT INTO tickets (order_id, pdf_path, generated_by) VALUES (?, ?, ?)";
$log_stmt = mysqli_prepare($conn, $log_sql);
$pdf_path = 'labels/shipping_label_' . $order['tracking_code'] . '.pdf';
mysqli_stmt_bind_param($log_stmt, "isi", $order_id, $pdf_path, $_SESSION['user_id']);
mysqli_stmt_execute($log_stmt);
?>
