<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login");
    exit();
}

// Include database and models
require_once 'config/database.php';
require_once 'models/Expense.php';
require_once 'models/Category.php';
require_once 'includes/utils.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$expense = new Expense($db);

// Get user ID from session
$user_id = $_SESSION["user_id"];

// Get export type
$export_type = isset($_GET['type']) ? $_GET['type'] : 'csv';

// Get filter parameters
$filter_category = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Get expenses based on filters
if ($filter_category) {
    $expenses = $expense->getByCategory($user_id, $filter_category);
} elseif ($filter_start_date && $filter_end_date) {
    $expenses = $expense->getByDateRange($user_id, $filter_start_date, $filter_end_date);
} else {
    $expenses = $expense->getAllByUser($user_id);
}

// Set filename
$filename = 'expenses_' . date('Y-m-d');

// Handle export based on type
if ($export_type === 'csv') {
    // Export as CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    // Create a file pointer
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add CSV header
    fputcsv($output, ['Date', 'Category', 'Description', 'Amount (₹)']);
    
    // Add data rows
    while ($row = $expenses->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['expense_date'],
            $row['category_name'],
            $row['description'],
            $row['amount']
        ]);
    }
    
    // Close the file pointer
    fclose($output);
    exit();
} elseif ($export_type === 'pdf') {
    // Check if TCPDF is available
    if (!file_exists('vendor/tecnickcom/tcpdf/tcpdf.php')) {
        // If TCPDF is not available, use a simple HTML to PDF approach
        
        // Set headers for PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
        
        // Start output buffering
        ob_start();
        
        // Output HTML content
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Expense Report</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                }
                h1 {
                    color: #007bff;
                    text-align: center;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                }
                .amount {
                    text-align: right;
                    font-weight: bold;
                }
                .total {
                    font-weight: bold;
                    background-color: #f8f9fa;
                }
            </style>
        </head>
        <body>
            <h1>Expense Report</h1>
            <p>Generated on: <?php echo date('F j, Y, g:i a'); ?></p>
            
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_amount = 0;
                    
                    while ($row = $expenses->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . $row['expense_date'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td class='amount'>" . number_format($row['amount'], 2) . "</td>";
                        echo "</tr>";
                        
                        $total_amount += $row['amount'];
                    }
                    ?>
                    <tr class="total">
                        <td colspan="3" style="text-align: right;">Total:</td>
                        <td class="amount"><?php echo number_format($total_amount, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </body>
        </html>
        <?php
        // Get the HTML content
        $html = ob_get_clean();
        
        // Try to use a PDF conversion library if available
        // For this example, we'll use a simple HTML to PDF approach
        // In a production environment, you should use a proper PDF library like TCPDF, FPDF, or Dompdf
        
        // For now, just output the HTML with PDF headers
        echo $html;
        exit();
    } else {
        // Use TCPDF if available
        require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Expense Tracker');
        $pdf->SetAuthor('Expense Tracker User');
        $pdf->SetTitle('Expense Report');
        $pdf->SetSubject('Expense Report');
        
        // Set default header data
        $pdf->SetHeaderData('', 0, 'Expense Report', 'Generated on: ' . date('F j, Y, g:i a'));
        
        // Set header and footer fonts
        $pdf->setHeaderFont(Array('helvetica', '', 10));
        $pdf->setFooterFont(Array('helvetica', '', 8));
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont('courier');
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Create table header
        $html = '<table border="1" cellpadding="5">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        // Add data rows
        $total_amount = 0;
        
        while ($row = $expenses->fetch(PDO::FETCH_ASSOC)) {
            $html .= '<tr>';
            $html .= '<td>' . $row['expense_date'] . '</td>';
            $html .= '<td>' . htmlspecialchars($row['category_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['description']) . '</td>';
            $html .= '<td align="right">' . number_format($row['amount'], 2) . '</td>';
            $html .= '</tr>';
            
            $total_amount += $row['amount'];
        }
        
        // Add total row
        $html .= '<tr style="background-color: #f8f9fa; font-weight: bold;">
                    <td colspan="3" align="right">Total:</td>
                    <td align="right">' . number_format($total_amount, 2) . '</td>
                  </tr>';
        
        $html .= '</tbody></table>';
        
        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output($filename . '.pdf', 'D');
        exit();
    }
}

// If we get here, redirect back to expenses page
header("Location: expenses");
exit();
?> 