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
            'recipient_email' => 'required|email',
            'notes' => 'nullable|string',
            'quotation_data' => 'required|array',
            'quotation_data.items' => 'required|array',
            'quotation_data.totalEstimatedCost' => 'required|numeric',
            'quotation_data.createdDate' => 'required|string',
            'quotation_data.createdBy' => 'required|string',
        ]);

        $user = $request->user();
        $school = $user->school;

        try {
            // Send email with quotation details
            Mail::send([], [], function ($message) use ($request, $school) {
                $message->to($request->recipient_email)
                        ->subject('Quotation Request - ' . count($request->quotation_data['items']) . ' Items - ' . ($school ? $school->name : 'Edumall System'))
                        ->html($this->buildEmailTemplate($request->all(), $school));
            });

            return response()->json([
                'message' => 'Quotation sent successfully',
                'details' => [
                    'recipient' => $request->recipient_email,
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
    private function buildEmailTemplate($data, $school)
    {
        $quotation = $data['quotation_data'];
        $items = $quotation['items'];
        $totalCost = $quotation['totalEstimatedCost'];
        $createdDate = $quotation['createdDate'];
        $createdBy = $quotation['createdBy'];
        $notes = $data['notes'] ?? '';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Quotation Request</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 20px; }
                .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .items-table th, .items-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                .items-table th { background-color: #f8f9fa; font-weight: bold; }
                .total { background-color: #e9ecef; padding: 15px; text-align: right; font-weight: bold; border-radius: 5px; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
                .school-info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>EDUMALL</h1>
                    <h2>Laboratory Management System</h2>
                    <h3>Quotation Request</h3>
                </div>

                <div class="content">
                    <div class="school-info">
                        <strong>From:</strong> ' . ($school ? $school->name : 'Edumall System') . '<br>
                        <strong>Requested By:</strong> ' . $createdBy . '<br>
                        <strong>Date:</strong> ' . $createdDate . '<br>
                        <strong>Quotation ID:</strong> ' . $data['quotation_id'] . '
                    </div>

                    <p>Dear Supplier,</p>

                    <p>We are requesting quotations for the following laboratory items that are running low in stock:</p>

                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Name</th>
                                <th>Quantity Needed</th>
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
                    </div>

                    <p>Please provide your best pricing and availability for these items. The estimated costs shown are based on our current market research.</p>';

        if (!empty($notes)) {
            $html .= '<p><strong>Additional Notes:</strong><br>' . nl2br(htmlspecialchars($notes)) . '</p>';
        }

        $html .= '
                    <p>We look forward to your prompt response and competitive pricing.</p>

                    <p>Best regards,<br>
                    ' . $createdBy . '<br>
                    ' . ($school ? $school->name : 'Edumall Laboratory Management') . '<br>
                    Laboratory Department</p>
                </div>

                <div class="footer">
                    <p>This email was sent from the Edumall Laboratory Management System.</p>
                    <p>Contact: info@edumall.com | Phone: +256 XXX XXX XXX</p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }
}
