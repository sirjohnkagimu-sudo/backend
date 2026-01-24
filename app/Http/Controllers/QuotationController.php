<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\School;

class QuotationController extends Controller
{
    /**
     * Send quotation email
     */
    public function sendQuotation(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|string',
            'notes' => 'required|string',
            'contact_details' => 'required|string',
            'quotation_data' => 'required|array',
            'quotation_data.items' => 'required|array',
            'quotation_data.totalEstimatedCost' => 'required|numeric',
            'quotation_data.createdDate' => 'required|string',
            'quotation_data.createdBy' => 'required|string',
        ]);

        $user = $request->user();
        $school = $user->school;

        try {
            // Send email with quotation details to Edumall
            Mail::send([], [], function ($message) use ($request, $school) {
                $message->to('edumallug@gmail.com')
                        ->subject('Quotation Request - ' . count($request->quotation_data['items']) . ' Items - ' . ($school ? $school->name : 'Edumall System'))
                        ->html($this->buildEmailTemplate($request->all(), $school));
            });

            return response()->json([
                'message' => 'Quotation sent successfully',
                'details' => [
                    'recipient' => 'edumallug@gmail.com',
                    'items_count' => count($request->quotation_data['items']),
                    'total_cost' => $request->quotation_data['totalEstimatedCost']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send quotation email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF content for quotation
     */
    private function generateQuotationPDF($quotationData, $school)
    {
        // This would generate the PDF content
        // For now, return a placeholder - in real implementation,
        // you'd use a PDF library like TCPDF or DomPDF
        return "PDF Content Placeholder";
    }

    /**
     * Build HTML email template
     */
   private function buildEmailTemplate($data, $school, $user = null)
{
   $quotation = $data['quotation_data'];
   $items = $quotation['items'];
   $totalCost = $quotation['totalEstimatedCost'];
   $createdDate = $quotation['createdDate'];
   $createdBy = $quotation['createdBy'];
   $notes = $data['notes'] ?? '';
   $contactDetails = $data['contact_details'] ?? '';
   $user = $data['user'] ?? null;

    $headerBg = 'https://i.imghippo.com/files/QaUM5275qQ.jpg';
    $logo = 'https://i.imghippo.com/files/ajv8989ujg.png';

    $html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Quotation Request</title>
<style>
body {
    margin: 0;
    padding: 0;
    background-color: #f2f4f7;
    font-family: Poppins, Helvetica, sans-serif;
}
.container {
    max-width: 640px;
    margin: 30px auto;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.header {
    position: relative;
    background: #B0E0E6;
    background-size: cover;
    background-position: center;
    padding: 50px 20px;
    text-align: center;
}
.header::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
}
.header-content {
    position: relative;
    z-index: 2;
}
.header img {
    max-width: 140px;
    margin-bottom: 15px;
}
.header h1 {
    margin: 0;
    font-size: 22px;
    letter-spacing: 1px;
    color: #ffffff;
}
.header p {
    margin: 6px 0 0;
    font-size: 14px;
    color: #e5e7eb;
}
.content {
    padding: 30px;
    color: #1f2937;
}
.info-box {
    background: #f9fafb;
    border-left: 4px solid #1E90FF;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 25px;
    font-size: 14px;
}
.items-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}
.items-table th {
    background: #f3f4f6;
    padding: 10px;
    text-align: left;
    font-size: 13px;
}
.items-table td {
    padding: 10px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 13px;
}
.total {
    background: #1E90FF;
    color: #ffffff;
    padding: 14px;
    border-radius: 6px;
    text-align: right;
    font-weight: bold;
    margin-top: 20px;
}
.footer {
    background: #0b1220;
    color: #9ca3af;
    padding: 25px;
    text-align: center;
    font-size: 12px;
}
.footer strong {
    color: #ffffff;
}
.footer a {
    color: #38bdf8;
    text-decoration: none;
}
</style>
</head>

<body>
<div class="container">

    <div class="header">
        <div class="header-content">
            <img src="' . $logo . '" alt="Edumall Logo">
            <h1>QUOTATION REQUEST</h1>
            <p>Laboratory Procurement</p>
        </div>
    </div>

    <div class="content">
        <div class="info-box">
            <strong>From:</strong> ' . ($school ? $school->name : 'Edumall System') . '<br>
            <strong>Requested By:</strong> ' . $createdBy . '<br>
            <strong>Contact Details:</strong> ' . htmlspecialchars($contactDetails) . '<br>
            <strong>Date:</strong> ' . $createdDate . '<br>
            <strong>Quotation ID:</strong> ' . $data['quotation_id'] . '
        </div>

        <p>Dear Supplier,</p>

        <p>We kindly request a quotation for the following laboratory items:</p>

        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Estimated Cost (UGX)</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($items as $index => $item) {
        $html .= '
                <tr>
                    <td>' . ($index + 1) . '</td>
                    <td>' . htmlspecialchars($item['name']) . '</td>
                    <td>' . $item['quantity'] . '</td>
                    <td>UGX ' . number_format($item['estimatedCost'], 2) . '</td>
                </tr>';
    }

    $html .= '
            </tbody>
        </table>

        <div class="total">
            Total Estimated Cost: UGX ' . number_format($totalCost, 2) . '
        </div>';

    if (!empty($notes)) {
        $html .= '
        <p><strong>Additional Notes:</strong><br>' . nl2br(htmlspecialchars($notes)) . '</p>';
    }

    $html .= '
        <p>We appreciate your prompt response and competitive pricing.</p>

        <p>
            Kind regards,<br>
            <strong>' . $createdBy . '</strong><br>
            ' . ($school ? $school->name : 'Edumall Laboratory Management') . '
        </p>
    </div>

    <div class="footer">
        <strong>Edumall Solutions Limited</strong><br>
        Laboratory Management System<br><br>
        📧 <a href="mailto:edumallug@gmail.com">edumallug@gmail.com</a> &nbsp;|&nbsp;
        ☎ +256 781 978 910<br><br>
        © ' . date('Y') . ' Edumall. All rights reserved.
    </div>

</div>
</body>
</html>';

    return $html;
}

}
