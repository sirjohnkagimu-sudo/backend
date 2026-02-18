<?php

namespace App\Http\Controllers;

use App\Models\BookTitle;
use App\Models\BookCopy;
use App\Models\Library;
use App\Models\LibraryStudent;
use App\Models\BorrowTransaction;
use App\Models\LibraryDonor;
use App\Models\LibraryDonation;
use App\Models\BookReservation;
use App\Models\LibraryAttendance;
use App\Models\TeacherAllocation;
use App\Models\BulkIssue;
use App\Models\LibraryClearance;
use App\Models\LostBookCharge;
use App\Models\LibraryPayment;
use App\Models\LibraryInvoice;
use App\Models\LibrarySettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LibraryController extends Controller
{
    // Get tenant_id from authenticated user
    protected function getTenantId()
    {
        return auth()->user()->tenant_id ?? request()->header('X-Tenant-ID');
    }

    // Transform book title to camelCase
    private function transformBookTitle($title)
    {
        if (!$title) return null;
        return [
            'id' => (string) $title->id,
            'isbn' => $title->isbn,
            'title' => $title->title,
            'author' => $title->author,
            'publisher' => $title->publisher,
            'year' => $title->year,
            'subject' => $title->subject,
            'class' => $title->class,
            'category' => $title->category,
            'replacementCost' => (float) $title->replacement_cost,
            'totalCopies' => $title->total_copies,
            'createdAt' => $title->created_at,
            'updatedAt' => $title->updated_at,
        ];
    }

    // Transform book copy to camelCase
    private function transformBookCopy($copy)
    {
        if (!$copy) return null;
        return [
            'id' => (string) $copy->id,
            'titleId' => (string) $copy->title_id,
            'barcode' => $copy->barcode,
            'copyNumber' => $copy->copy_number,
            'status' => $copy->status,
            'condition' => $copy->condition,
            'location' => $copy->location,
            'donatedBy' => $copy->donated_by,
            'donationDate' => $copy->donation_date,
            'acquiredDate' => $copy->acquired_date,
            'lastInspectionDate' => $copy->last_inspection_date,
            'chainOfCustody' => $copy->chain_of_custody ?? [],
            'currentHolder' => $copy->current_holder_id ? (string) $copy->current_holder_id : null,
            'lastCheckoutDate' => $copy->last_checkout_date,
            'expectedReturnDate' => $copy->expected_return_date,
        ];
    }

    // Transform student to camelCase
    private function transformStudent($student)
    {
        if (!$student) return null;
        return [
            'id' => (string) $student->id,
            'studentId' => (string) $student->student_id,
            'studentName' => $student->first_name . ' ' . $student->last_name,
            'firstName' => $student->first_name,
            'lastName' => $student->last_name,
            'studentClass' => $student->class,
            'gender' => $student->gender,
            'admissionNumber' => $student->admission_number,
            'libraryCardNumber' => $student->library_card_number,
            'phoneNumber' => $student->phone_number,
            'emergencyContact' => $student->emergency_contact,
            'clearanceStatus' => $student->clearance_status,
            'clearanceNotes' => $student->clearance_notes,
            'borrowedBooks' => [],
            'overdueBooks' => $student->overdue_books,
            'lostBookBalance' => (float) $student->lost_book_balance,
            'joinedDate' => $student->joined_date,
            'lastVisit' => $student->last_visit,
            'totalVisits' => $student->total_visits,
            'totalReadingHours' => (float) $student->total_reading_hours,
        ];
    }

    // Transform transaction to camelCase
    private function transformTransaction($transaction)
    {
        if (!$transaction) return null;
        return [
            'id' => (string) $transaction->id,
            'copyId' => (string) $transaction->copy_id,
            'studentId' => (string) $transaction->student_id,
            'studentName' => $transaction->student ? $transaction->student->first_name . ' ' . $transaction->student->last_name : '',
            'studentClass' => $transaction->student->class ?? '',
            'issuedBy' => (string) $transaction->issued_by,
            'issuedDate' => $transaction->issued_date,
            'dueDate' => $transaction->due_date,
            'returnDate' => $transaction->return_date,
            'term' => $transaction->term,
            'academicYear' => $transaction->academic_year,
            'status' => $transaction->status,
            'conditionOnReturn' => $transaction->condition_on_return,
            'notes' => $transaction->notes,
        ];
    }

    // ============ BOOK TITLES ============

    public function getBookTitles()
    {
        $titles = BookTitle::where('tenant_id', $this->getTenantId())->get();
        return response()->json($titles->map(fn($t) => $this->transformBookTitle($t)));
    }

    public function createBookTitle(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();

        $title = BookTitle::create($data);
        return response()->json($this->transformBookTitle($title), 201);
    }

    public function updateBookTitle(Request $request, $id)
    {
        $title = BookTitle::where('tenant_id', $this->getTenantId())->findOrFail($id);
        $title->update($request->all());
        return response()->json($this->transformBookTitle($title));
    }

    public function deleteBookTitle($id)
    {
        $title = BookTitle::where('tenant_id', $this->getTenantId())->findOrFail($id);
        $title->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    /**
     * Bulk import book titles
     */
    public function bulkImportBookTitles(Request $request)
    {
        $validated = $request->validate([
            'books' => 'required|array',
            'books.*.isbn' => 'required|string|max:255',
            'books.*.title' => 'required|string|max:255',
            'books.*.author' => 'required|string|max:255',
            'books.*.publisher' => 'nullable|string|max:255',
            'books.*.year' => 'nullable|integer',
            'books.*.subject' => 'required|string|max:255',
            'books.*.class' => 'required|string|max:50',
            'books.*.category' => 'required|in:textbook,reference,fiction,magazine,journal',
            'books.*.replacementCost' => 'nullable|numeric|min:0',
            'books.*.totalCopies' => 'required|integer|min:1',
        ]);

        $created = [];
        $tenantId = $this->getTenantId();

        foreach ($validated['books'] as $bookData) {
            $bookData['tenant_id'] = $tenantId;
            $bookData['replacement_cost'] = $bookData['replacementCost'] ?? 0;
            $bookData['total_copies'] = $bookData['totalCopies'];
            unset($bookData['replacementCost'], $bookData['totalCopies']);

            $title = BookTitle::create($bookData);
            $created[] = $this->transformBookTitle($title);
        }

        return response()->json([
            'message' => 'Successfully imported ' . count($created) . ' book titles',
            'count' => count($created),
            'books' => $created,
        ], 201);
    }

    // ============ BOOK COPIES ============

    public function getBookCopies()
    {
        $copies = BookCopy::with('title')->where('tenant_id', $this->getTenantId())->get();
        return response()->json($copies->map(fn($c) => $this->transformBookCopy($c)));
    }

    public function createBookCopy(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();

        $copy = BookCopy::create($data);
        return response()->json($this->transformBookCopy($copy), 201);
    }

    public function updateBookCopy(Request $request, $id)
    {
        $copy = BookCopy::where('tenant_id', $this->getTenantId())->findOrFail($id);
        $copy->update($request->all());
        return response()->json($this->transformBookCopy($copy));
    }

    public function deleteBookCopy($id)
    {
        $copy = BookCopy::where('tenant_id', $this->getTenantId())->findOrFail($id);
        $copy->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    // ============ STUDENTS ============

    public function getStudents()
    {
        $students = LibraryStudent::where('tenant_id', $this->getTenantId())->get();
        return response()->json($students->map(fn($s) => $this->transformStudent($s)));
    }

    public function createStudent(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();

        $student = LibraryStudent::create($data);
        return response()->json($this->transformStudent($student), 201);
    }

    public function updateStudent(Request $request, $id)
    {
        $student = LibraryStudent::where('tenant_id', $this->getTenantId())->findOrFail($id);
        $student->update($request->all());
        return response()->json($this->transformStudent($student));
    }

    public function deleteStudent($id)
    {
        $student = LibraryStudent::where('tenant_id', $this->getTenantId())->findOrFail($id);
        $student->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    // ============ BORROW TRANSACTIONS ============

    public function getTransactions()
    {
        $transactions = BorrowTransaction::with(['copy.title', 'student'])
            ->where('tenant_id', $this->getTenantId())
            ->get();
        return response()->json($transactions->map(fn($t) => $this->transformTransaction($t)));
    }

    public function issueBook(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();
        $data['issued_by'] = Auth::id();
        $data['issued_date'] = now()->toDateString();
        $data['status'] = 'active';

        $transaction = BorrowTransaction::create($data);

        // Update book copy status
        $copy = BookCopy::findOrFail($data['copy_id']);
        $copy->update([
            'status' => 'borrowed',
            'current_holder_id' => $data['student_id'],
            'current_holder_type' => 'student',
            'last_checkout_date' => now()->toDateString(),
            'expected_return_date' => $data['due_date'],
        ]);

        return response()->json($this->transformTransaction($transaction->fresh(['copy', 'student'])), 201);
    }

    public function returnBook(Request $request, $id)
    {
        $transaction = BorrowTransaction::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $transaction->update([
            'status' => 'returned',
            'return_date' => now()->toDateString(),
            'condition_on_return' => $request->input('condition', 'good'),
        ]);

        // Update book copy status
        $copy = BookCopy::findOrFail($transaction->copy_id);
        $copy->update([
            'status' => 'available',
            'current_holder_id' => null,
            'current_holder_type' => null,
            'condition' => $request->input('condition', $copy->condition),
        ]);

        return response()->json($this->transformTransaction($transaction->fresh(['copy', 'student'])));
    }

    // ============ DONORS ============

    public function getDonors()
    {
        $donors = LibraryDonor::where('tenant_id', $this->getTenantId())->get();
        return response()->json($donors->map(fn($d) => [
            'id' => (string) $d->id,
            'name' => $d->name,
            'type' => $d->type,
            'contactPerson' => $d->contact_person,
            'email' => $d->email,
            'phone' => $d->phone,
            'address' => $d->address,
            'donationCount' => $d->donation_count,
            'totalBooksDonated' => $d->total_books_donated,
            'createdDate' => $d->created_at,
            'notes' => $d->notes,
        ]));
    }

    public function createDonor(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();

        $donor = LibraryDonor::create($data);
        return response()->json([
            'id' => (string) $donor->id,
            'name' => $donor->name,
            'type' => $donor->type,
        ], 201);
    }

    // ============ DONATIONS ============

    public function getDonations()
    {
        $donations = LibraryDonation::with('donor')
            ->where('tenant_id', $this->getTenantId())
            ->get();
        return response()->json($donations->map(fn($d) => [
            'id' => (string) $d->id,
            'donorId' => (string) $d->donor_id,
            'donorName' => $d->donor ? $d->donor->name : '',
            'donationDate' => $d->donation_date,
            'type' => $d->type,
            'bookCopies' => $d->book_copies,
            'totalBooks' => $d->total_books,
            'totalValue' => (float) $d->total_value,
            'condition' => $d->condition,
            'purpose' => $d->purpose,
            'receivedBy' => (string) $d->received_by,
            'status' => $d->status,
            'certificateNumber' => $d->certificate_number,
            'certificateDate' => $d->certificate_date,
            'acknowledgementLetterSent' => $d->acknowledgement_letter_sent,
            'notes' => $d->notes,
        ]));
    }

    public function createDonation(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();
        $data['received_by'] = Auth::id();

        $donation = LibraryDonation::create($data);
        return response()->json(['id' => (string) $donation->id], 201);
    }

    // ============ RESERVATIONS ============

    public function getReservations()
    {
        $reservations = BookReservation::with('bookTitle')
            ->where('tenant_id', $this->getTenantId())
            ->get();
        return response()->json($reservations->map(fn($r) => [
            'id' => (string) $r->id,
            'requesterId' => $r->requester_id ? (string) $r->requester_id : '',
            'requesterName' => $r->requester_name,
            'requesterType' => $r->requester_type,
            'subject' => $r->subject,
            'topic' => $r->topic,
            'bookTitleId' => $r->book_title_id ? (string) $r->book_title_id : '',
            'className' => $r->class_name,
            'numberOfCopies' => $r->number_of_copies,
            'purpose' => $r->purpose,
            'requestedDate' => $r->requested_date,
            'requiredDate' => $r->required_date,
            'status' => $r->status,
            'approvedBy' => $r->approved_by ? (string) $r->approved_by : '',
            'approvedDate' => $r->approved_date,
            'rejectionReason' => $r->rejection_reason,
            'fulfilledDate' => $r->fulfilled_date,
            'notes' => $r->notes,
        ]));
    }

    public function createReservation(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();

        $reservation = BookReservation::create($data);
        return response()->json(['id' => (string) $reservation->id], 201);
    }

    public function approveReservation(Request $request, $id)
    {
        $reservation = BookReservation::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $reservation->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_date' => now()->toDateString(),
        ]);

        return response()->json(['message' => 'Approved successfully']);
    }

    // ============ ATTENDANCE ============

    public function getAttendance()
    {
        $attendance = LibraryAttendance::with('student')
            ->where('tenant_id', $this->getTenantId())
            ->get();
        return response()->json($attendance->map(fn($a) => [
            'id' => (string) $a->id,
            'studentId' => (string) $a->student_id,
            'studentName' => $a->student ? $a->student->first_name . ' ' . $a->student->last_name : '',
            'studentClass' => $a->student->class ?? '',
            'entryTime' => $a->entry_time,
            'exitTime' => $a->exit_time,
            'purpose' => $a->purpose,
            'readingHours' => (float) $a->reading_hours,
            'booksRead' => $a->books_read,
            'qrCodeScanned' => $a->qr_code_scanned,
            'gate' => $a->gate,
        ]));
    }

    public function recordAttendance(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();

        $attendance = LibraryAttendance::create($data);
        return response()->json(['id' => (string) $attendance->id], 201);
    }

    // ============ TEACHER ALLOCATIONS ============

    public function getTeacherAllocations()
    {
        $allocations = TeacherAllocation::with('bookTitle')
            ->where('tenant_id', $this->getTenantId())
            ->get();
        return response()->json($allocations->map(fn($a) => [
            'id' => (string) $a->id,
            'teacherId' => $a->teacher_id ? (string) $a->teacher_id : '',
            'teacherName' => $a->teacher_name,
            'subject' => $a->subject,
            'className' => $a->class_name,
            'bookTitleId' => (string) $a->book_title_id,
            'copiesAllocated' => $a->copies_allocated,
            'copiesReturned' => $a->copies_returned,
            'allocationDate' => $a->allocation_date,
            'expectedReturnDate' => $a->expected_return_date,
            'status' => $a->status,
            'confirmations' => $a->confirmations ?? [],
        ]));
    }

    public function createTeacherAllocation(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();

        $allocation = TeacherAllocation::create($data);
        return response()->json(['id' => (string) $allocation->id], 201);
    }

    // ============ BULK ISSUES ============

    public function getBulkIssues()
    {
        $issues = BulkIssue::with('bookTitle')
            ->where('tenant_id', $this->getTenantId())
            ->get();
        return response()->json($issues->map(fn($i) => [
            'id' => (string) $i->id,
            'bookTitleId' => (string) $i->book_title_id,
            'class' => $i->class,
            'term' => $i->term,
            'academicYear' => $i->academic_year,
            'issueDate' => $i->issue_date,
            'dueDate' => $i->due_date,
            'issuedBy' => (string) $i->issued_by,
            'status' => $i->status,
            'totalCopies' => $i->total_copies,
            'issuedCopies' => $i->issued_copies,
            'pendingCopies' => $i->pending_copies,
        ]));
    }

    public function createBulkIssue(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();
        $data['issued_by'] = Auth::id();

        $issue = BulkIssue::create($data);
        return response()->json(['id' => (string) $issue->id], 201);
    }

    // ============ CLEARANCES ============

    public function getClearances()
    {
        $clearances = LibraryClearance::with('student')
            ->where('tenant_id', $this->getTenantId())
            ->get();
        return response()->json($clearances->map(fn($c) => [
            'id' => (string) $c->id,
            'studentId' => (string) $c->student_id,
            'studentName' => $c->student ? $c->student->first_name . ' ' . $c->student->last_name : '',
            'studentClass' => $c->student->class ?? '',
            'term' => $c->term,
            'academicYear' => $c->academic_year,
            'status' => $c->status,
            'borrowedBooks' => $c->borrowed_books ?? [],
            'totalBorrowed' => $c->total_borrowed,
            'totalReturned' => $c->total_returned,
            'totalOutstanding' => $c->total_outstanding,
            'lostBookCharges' => [],
            'totalLostBookFees' => (float) $c->total_lost_book_fees,
            'totalPaid' => (float) $c->total_paid,
            'totalBalance' => (float) $c->total_balance,
            'clearedBy' => $c->cleared_by ? (string) $c->cleared_by : '',
            'clearedDate' => $c->cleared_date,
            'blockageReason' => $c->blockage_reason,
            'reportCardBlocked' => $c->report_card_blocked,
        ]));
    }

    public function createClearance(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();
        $data['cleared_by'] = Auth::id();

        $clearance = LibraryClearance::create($data);
        return response()->json(['id' => (string) $clearance->id], 201);
    }

    // ============ PAYMENTS ============

    public function getPayments()
    {
        $payments = LibraryPayment::with('student')
            ->where('tenant_id', $this->getTenantId())
            ->get();
        return response()->json($payments->map(fn($p) => [
            'id' => (string) $p->id,
            'studentId' => (string) $p->student_id,
            'studentName' => $p->student ? $p->student->first_name . ' ' . $p->student->last_name : '',
            'type' => $p->type,
            'referenceType' => $p->reference_type,
            'referenceId' => $p->reference_id ? (string) $p->reference_id : '',
            'amount' => (float) $p->amount,
            'paymentMethod' => $p->payment_method,
            'transactionId' => $p->transaction_id,
            'receiptNumber' => $p->receipt_number,
            'collectedBy' => (string) $p->collected_by,
            'collectionDate' => $p->collection_date,
            'notes' => $p->notes,
        ]));
    }

    public function recordPayment(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();
        $data['collected_by'] = Auth::id();
        $data['collection_date'] = now()->toDateString();

        $payment = LibraryPayment::create($data);

        // Update related invoice if exists
        if (isset($data['reference_type']) && $data['reference_type'] === 'invoice' && isset($data['reference_id'])) {
            $invoice = LibraryInvoice::find($data['reference_id']);
            if ($invoice) {
                $invoice->update([
                    'amount_paid' => $invoice->amount_paid + $data['amount'],
                    'balance' => $invoice->balance - $data['amount'],
                    'status' => ($invoice->balance - $data['amount']) <= 0 ? 'paid' : 'partial',
                ]);
            }
        }

        return response()->json(['id' => (string) $payment->id], 201);
    }

    // ============ INVOICES ============

    public function getInvoices()
    {
        $invoices = LibraryInvoice::with('student')
            ->where('tenant_id', $this->getTenantId())
            ->get();
        return response()->json($invoices->map(fn($i) => [
            'id' => (string) $i->id,
            'invoiceNumber' => $i->invoice_number,
            'studentId' => (string) $i->student_id,
            'studentName' => $i->student ? $i->student->first_name . ' ' . $i->student->last_name : '',
            'studentClass' => $i->student->class ?? '',
            'items' => $i->items ?? [],
            'totalAmount' => (float) $i->total_amount,
            'amountPaid' => (float) $i->amount_paid,
            'balance' => (float) $i->balance,
            'status' => $i->status,
            'createdDate' => $i->created_date,
            'dueDate' => $i->due_date,
            'paidDate' => $i->paid_date,
            'cancelledDate' => $i->cancelled_date,
            'createdBy' => (string) $i->created_by,
        ]));
    }

    public function createInvoice(Request $request)
    {
        $data = $request->all();
        $data['tenant_id'] = $this->getTenantId();
        $data['created_by'] = Auth::id();
        $data['invoice_number'] = 'INV-' . date('Y') . '-' . Str::random(6);

        $invoice = LibraryInvoice::create($data);
        return response()->json(['id' => (string) $invoice->id, 'invoiceNumber' => $invoice->invoice_number], 201);
    }

    // ============ SETTINGS ============

    public function getSettings()
    {
        $settings = LibrarySettings::where('tenant_id', $this->getTenantId())->first();

        if (!$settings) {
            // Create default settings
            $settings = LibrarySettings::create([
                'tenant_id' => $this->getTenantId(),
                'academic_year' => date('Y'),
                'current_term' => 'Term 1',
                'term_dates' => json_encode([
                    'term1' => ['start' => date('Y-01-15'), 'end' => date('Y-04-15')],
                    'term2' => ['start' => date('Y-05-06'), 'end' => date('Y-08-09')],
                    'term3' => ['start' => date('Y-09-09'), 'end' => date('Y-12-13')],
                ]),
                'borrowing_rules' => json_encode([
                    'maxBooksPerStudent' => 3,
                    'loanPeriodDays' => 90,
                    'maxRenewals' => 1,
                    'finePerDay' => 500,
                    'replacementFeeMultiplier' => 1.5,
                ]),
                'clearance_settings' => json_encode([
                    'autoClearEligible' => true,
                    'blockReportCards' => true,
                    'requireTeacherSignOff' => false,
                ]),
                'contact_info' => json_encode([
                    'email' => 'library@school.ug',
                    'phone' => '+256-700-000000',
                    'openingHours' => '7:00 AM - 6:00 PM',
                ]),
            ]);
        }

        return response()->json([
            'schoolName' => 'Edumall High School',
            'academicYear' => $settings->academic_year,
            'currentTerm' => $settings->current_term,
            'termDates' => is_string($settings->term_dates) ? json_decode($settings->term_dates, true) : $settings->term_dates,
            'borrowingRules' => is_string($settings->borrowing_rules) ? json_decode($settings->borrowing_rules, true) : $settings->borrowing_rules,
            'clearanceSettings' => is_string($settings->clearance_settings) ? json_decode($settings->clearance_settings, true) : $settings->clearance_settings,
            'contactInfo' => is_string($settings->contact_info) ? json_decode($settings->contact_info, true) : $settings->contact_info,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $settings = LibrarySettings::where('tenant_id', $this->getTenantId())->first();

        if ($settings) {
            $settings->update($request->all());
        } else {
            $data = $request->all();
            $data['tenant_id'] = $this->getTenantId();
            $settings = LibrarySettings::create($data);
        }

        return response()->json(['message' => 'Settings updated']);
    }

    // ============ DASHBOARD STATS ============

    public function getStats()
    {
        $tenantId = $this->getTenantId();

        $totalBooks = BookCopy::where('tenant_id', $tenantId)->count();
        $totalTitles = BookTitle::where('tenant_id', $tenantId)->count();
        $totalStudents = LibraryStudent::where('tenant_id', $tenantId)->count();

        $activeBorrowers = BorrowTransaction::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->distinct('student_id')
            ->count('student_id');

        $today = now()->toDateString();
        $todayVisits = LibraryAttendance::where('tenant_id', $tenantId)
            ->whereDate('entry_time', $today)
            ->count();

        $weekAgo = now()->subWeek()->toDateString();
        $weekVisits = LibraryAttendance::where('tenant_id', $tenantId)
            ->whereDate('entry_time', '>=', $weekAgo)
            ->count();

        $overdueBooks = BorrowTransaction::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('due_date', '<', $today)
            ->count();

        $lostBooks = BookCopy::where('tenant_id', $tenantId)
            ->where('status', 'lost')
            ->count();

        $damagedBooks = BookCopy::where('tenant_id', $tenantId)
            ->whereIn('condition', ['torn', 'missing-pages'])
            ->count();

        $pendingClearances = LibraryClearance::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $outstandingFees = LibraryInvoice::where('tenant_id', $tenantId)
            ->sum('balance');

        return response()->json([
            'totalBooks' => $totalBooks,
            'totalTitles' => $totalTitles,
            'totalStudents' => $totalStudents,
            'activeBorrowers' => $activeBorrowers,
            'todayVisits' => $todayVisits,
            'weekVisits' => $weekVisits,
            'overdueBooks' => $overdueBooks,
            'lostBooks' => $lostBooks,
            'damagedBooks' => $damagedBooks,
            'pendingClearances' => $pendingClearances,
            'outstandingFees' => (float) $outstandingFees,
            'lowStockSubjects' => [],
        ]);
    }

    // ============ LEGACY LIBRARY (PRODUCTS) METHODS ============
    // These methods handle the old library products system (textbooks, etc.)

    /**
     * Display a listing of library products (web route)
     */
    public function index()
    {
        session(['title' => 'Library Products']);
        $libraries = Library::all();
        return view('libraries.index', compact('libraries'));
    }

    /**
     * Show the form for creating a new library product (web route)
     */
    public function create()
    {
        return view('libraries.create');
    }

    /**
     * Store a newly created library product (web route)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'nullable|array',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer|min:0',
            'condition' => 'required|in:new,old',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'desc' => 'nullable|string',
        ]);

        $library = new Library();
        $library->name = $validated['name'];
        $library->category = $validated['category'];
        $library->color = $validated['color'] ?? null;
        $library->brand = $validated['brand'] ?? null;
        $library->in_stock = $validated['in_stock'] ?? 0;
        $library->condition = $validated['condition'];
        $library->price = $validated['price'];
        $library->discount = $validated['discount'] ?? 0;
        $library->desc = $validated['desc'] ?? null;

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('images/libraries', 'public');
            $library->avatar = $avatarPath;
        }

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('images/libraries', 'public');
            }
            $library->images = json_encode($imagePaths);
        }

        $library->save();

        return redirect()->route('index.libraries')->with('success', 'Library product created successfully.');
    }

    /**
     * Show the form for editing a library product (web route)
     */
    public function edit(Library $library)
    {
        return view('libraries.edit', compact('library'));
    }

    /**
     * Update a library product (web route)
     */
    public function update(Request $request, Library $library)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'nullable|array',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer|min:0',
            'condition' => 'required|in:new,old',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'desc' => 'nullable|string',
        ]);

        $library->name = $validated['name'];
        $library->category = $validated['category'];
        $library->color = $validated['color'] ?? null;
        $library->brand = $validated['brand'] ?? null;
        $library->in_stock = $validated['in_stock'] ?? 0;
        $library->condition = $validated['condition'];
        $library->price = $validated['price'];
        $library->discount = $validated['discount'] ?? 0;
        $library->desc = $validated['desc'] ?? null;

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($library->avatar) {
                \Storage::delete('public/' . $library->avatar);
            }
            $avatarPath = $request->file('avatar')->store('images/libraries', 'public');
            $library->avatar = $avatarPath;
        }

        if ($request->hasFile('images')) {
            // Delete old images
            if ($library->images) {
                foreach (json_decode($library->images, true) ?? [] as $oldImage) {
                    \Storage::delete('public/' . $oldImage);
                }
            }
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('images/libraries', 'public');
            }
            $library->images = json_encode($imagePaths);
        }

        $library->save();

        return redirect()->route('index.libraries')->with('success', 'Library product updated successfully.');
    }

    /**
     * Remove a library product (web route)
     */
    public function destroy(Library $library)
    {
        // Delete avatar
        if ($library->avatar) {
            \Storage::delete('public/' . $library->avatar);
        }

        // Delete images
        if ($library->images) {
            foreach (json_decode($library->images, true) ?? [] as $image) {
                \Storage::delete('public/' . $image);
            }
        }

        $library->delete();

        return redirect()->route('index.libraries')->with('success', 'Library product deleted successfully.');
    }

    // ============ API METHODS FOR LIBRARY PRODUCTS ============

    /**
     * Get all library products (API)
     */
    public function apiIndex(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Library::query();

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('desc', 'like', "%{$search}%");
            });
        }

        // Filter by condition
        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }

        // Filter by in_stock status
        if ($request->has('in_stock')) {
            if ($request->in_stock === 'low') {
                $query->where('in_stock', '>', 0)->where('in_stock', '<=', 5);
            } elseif ($request->in_stock === 'out') {
                $query->where('in_stock', 0);
            } elseif ($request->in_stock === 'available') {
                $query->where('in_stock', '>', 0);
            }
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $libraries = $query->paginate($perPage);

        return response()->json([
            'data' => $libraries->items(),
            'meta' => [
                'current_page' => $libraries->currentPage(),
                'last_page' => $libraries->lastPage(),
                'per_page' => $libraries->perPage(),
                'total' => $libraries->total(),
            ]
        ]);
    }

    /**
     * Get all library products (API - named route)
     */
    public function getLibraries(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->apiIndex($request);
    }

    /**
     * Store a new library product (API)
     */
    public function apiStore(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'condition' => 'required|in:new,old',
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'desc' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);

        $library = Library::create($validated);

        return response()->json($library, 201);
    }

    /**
     * Get a specific library product (API)
     */
    public function apiShow($id): \Illuminate\Http\JsonResponse
    {
        $library = Library::findOrFail($id);
        return response()->json($library);
    }

    /**
     * Update a library product (API)
     */
    public function apiUpdate(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $library = Library::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'condition' => 'sometimes|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'desc' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);

        $library->update($validated);

        return response()->json($library);
    }

    /**
     * Delete a library product (API)
     */
    public function apiDestroy($id): \Illuminate\Http\JsonResponse
    {
        $library = Library::findOrFail($id);

        // Delete the avatar image
        if ($library->avatar) {
            \Storage::delete('public/' . $library->avatar);
        }

        // Delete the images
        if ($library->images) {
            foreach (json_decode($library->images, true) ?? [] as $image) {
                \Storage::delete('public/' . $image);
            }
        }

        $library->delete();

        return response()->json(['message' => 'Library product deleted successfully']);
    }

    /**
     * Bulk import library products (Excel/CSV)
     */
    public function bulkImport(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.category' => 'required|string|max:255',
            'items.*.color' => 'nullable|string|max:255',
            'items.*.brand' => 'nullable|string|max:255',
            'items.*.in_stock' => 'nullable|integer|min:0',
            'items.*.min_quantity' => 'nullable|integer|min:0',
            'items.*.condition' => 'required|in:new,old',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.desc' => 'nullable|string',
            'items.*.location_id' => 'nullable|integer',
        ]);

        $items = $validated['items'];
        $created = [];

        foreach ($items as $itemData) {
            $created[] = Library::create($itemData);
        }

        return response()->json([
            'message' => 'Successfully imported ' . count($created) . ' library products',
            'count' => count($created),
            'items' => $created,
        ], 201);
    }

    /**
     * Get library products by location
     */
    public function getByLocation($locationId): \Illuminate\Http\JsonResponse
    {
        $items = Library::where('location_id', $locationId)->get();
        return response()->json($items);
    }

    /**
     * Get low stock library products
     */
    public function lowStock(): \Illuminate\Http\JsonResponse
    {
        $items = Library::whereColumn('in_stock', '<=', 'min_quantity')
            ->orWhere(function ($query) {
                $query->where('in_stock', '<=', 5)
                      ->whereNotNull('min_quantity');
            })
            ->get();

        return response()->json($items);
    }
}
