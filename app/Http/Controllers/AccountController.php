<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportTransactionsRequest;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountController extends Controller
{
    public function __construct(private AccountService $accountService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Account::class);
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.accounts.index', $this->accountService->getAccountIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Account::class);

        return view('admin.accounts.create', $this->accountService->prepareCreateFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccountRequest $request)
    {
        $this->authorize('create', Account::class);
        $account = $this->accountService->createAccount($request->validated());

        if ($request->boolean('from_company')) {
            return redirect()
                ->route('companies.show', $account->company_id)
                ->with('success', __('Account created successfully.'));
        }

        return redirect()->route('accounts.index')->with('success', __('Account created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Account $account): View
    {
        $this->authorize('view', $account);
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        $filters = [
            'type' => $request->string('filter_type')->toString() ?: null,
            'status' => $request->string('filter_status')->toString() ?: null,
            'category_id' => $request->integer('filter_category_id') ?: null,
            'client_id' => $request->integer('filter_client_id') ?: null,
            'date_from' => $request->string('filter_date_from')->toString() ?: null,
            'date_to' => $request->string('filter_date_to')->toString() ?: null,
        ];

        // Remove empty filter values
        $filters = array_filter($filters, static fn ($value) => $value !== null && $value !== '');

        return view('admin.accounts.show', $this->accountService->prepareShowData($account, $searchValue, 15, $filters));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account): View
    {
        $this->authorize('update', $account);

        return view('admin.accounts.edit', $this->accountService->prepareEditFormData($account));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccountRequest $request, Account $account)
    {
        $this->authorize('update', $account);
        $this->accountService->updateAccount($account, $request->validated());

        return redirect()->route('accounts.index')->with('success', __('Account updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $this->authorize('delete', $account);
        $this->accountService->deleteAccount($account);

        return redirect()->route('accounts.index')->with('success', __('Account deleted successfully.'));
    }

    /**
     * Export transactions to CSV.
     */
    public function exportTransactions(Request $request, Account $account): StreamedResponse
    {
        $this->authorize('view', $account);
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        $filters = [
            'type' => $request->string('filter_type')->toString() ?: null,
            'status' => $request->string('filter_status')->toString() ?: null,
            'category_id' => $request->integer('filter_category_id') ?: null,
            'client_id' => $request->integer('filter_client_id') ?: null,
            'date_from' => $request->string('filter_date_from')->toString() ?: null,
            'date_to' => $request->string('filter_date_to')->toString() ?: null,
        ];

        // Remove empty filter values
        $filters = array_filter($filters, static fn ($value) => $value !== null && $value !== '');

        $transactions = $this->accountService->getTransactionsForExport($account, $searchValue, $filters);

        $filename = 'transactions_'.Str::slug($account->name).'_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = static function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            fputcsv($file, [
                '#',
                __('Type'),
                __('Transaction ID'),
                __('Amount'),
                __('Category'),
                __('Client'),
                __('Status'),
                __('Date'),
                __('Description'),
                __('Created By'),
                __('Approved By'),
            ]);

            // Data rows
            foreach ($transactions as $index => $transaction) {
                fputcsv($file, [
                    $index + 1,
                    ucfirst($transaction->type),
                    $transaction->transaction_id ?? '',
                    number_format((float) $transaction->amount, 2),
                    $transaction->category->name ?? '',
                    $transaction->client->name ?? '',
                    ucfirst($transaction->status ?? 'pending'),
                    $transaction->date?->format('Y-m-d') ?? '',
                    $transaction->description ?? '',
                    $transaction->creator->name ?? '',
                    $transaction->approver->name ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download sample Excel template for importing transactions with dropdowns.
     */
    public function downloadSampleCsv(Account $account): StreamedResponse
    {
        $this->authorize('view', $account);
        $this->authorize('create', \App\Models\Transaction::class);

        // Get dynamic data based on account and user permissions
        $categories = $this->accountService->getCategoriesForImport($account);
        $clients = $this->accountService->getClients($account);
        $statuses = $this->accountService->getStatusOptions();

        // Filter types based on user permissions
        $types = $this->accountService->getTypeOptionsForUser();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            __('Transaction ID'),
            __('Type'),
            __('Amount'),
            __('Category'),
            __('Client'),
            __('Status'),
            __('Date'),
            __('Description'),
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Style header row
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(18); // Transaction ID
        $sheet->getColumnDimension('B')->setWidth(12); // Type
        $sheet->getColumnDimension('C')->setWidth(15); // Amount
        $sheet->getColumnDimension('D')->setWidth(25); // Category
        $sheet->getColumnDimension('E')->setWidth(20); // Client
        $sheet->getColumnDimension('F')->setWidth(12); // Status
        $sheet->getColumnDimension('G')->setWidth(12); // Date
        $sheet->getColumnDimension('H')->setWidth(40); // Description

        // Add sample data rows
        $sampleData = [
            ['', 'income', '1000.00', '', '', 'pending', now()->format('Y-m-d'), __('Sample income transaction')],
            ['', 'expense', '500.00', '', '', 'pending', now()->format('Y-m-d'), __('Sample expense transaction')],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Apply data validation (dropdowns) to all data rows (starting from row 2, up to row 1000)
        $lastRow = 1000;

        // Transaction ID (Column A) - optional, text input
        // No validation needed, just a text field

        // Type dropdown (Column B) - will be auto-filled based on category
        // First, set up the category sheet structure, then come back to set type formulas
        $typeValues = implode(',', array_keys($types));

        // Category dropdown (Column D) - using helper column approach
        // Create a helper sheet with all categories and their types
        $categorySheet = $spreadsheet->createSheet();
        $categorySheet->setTitle('Categories');
        $incomeCategories = array_filter($categories, fn ($cat) => $cat['type'] === 'income');
        $expenseCategories = array_filter($categories, fn ($cat) => $cat['type'] === 'expense');

        // Put all categories in column A with their types in column B
        $allCategoryNames = [];
        $categoryTypes = []; // Track which type each category is

        // Add income categories
        $incomeNames = array_column($incomeCategories, 'name');
        foreach ($incomeNames as $name) {
            $allCategoryNames[] = $name;
            $categoryTypes[] = 'income';
        }

        // Add expense categories
        $expenseNames = array_column($expenseCategories, 'name');
        foreach ($expenseNames as $name) {
            $allCategoryNames[] = $name;
            $categoryTypes[] = 'expense';
        }

        // Write all categories to the Categories sheet with types
        if (! empty($allCategoryNames)) {
            $categorySheet->setCellValue('A1', 'Category Name');
            $categorySheet->setCellValue('B1', 'Type');
            $categorySheet->getStyle('A1:B1')->getFont()->setBold(true);
            $categorySheet->getStyle('A1:B1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            foreach ($allCategoryNames as $index => $name) {
                $row = $index + 2;
                $categorySheet->setCellValue('A'.$row, $name);
                $categorySheet->setCellValue('B'.$row, $categoryTypes[$index]);
            }
            $categoryEndRow = count($allCategoryNames) + 1;
        } else {
            $categoryEndRow = 1;
        }

        // Apply category validation - show all categories (validation happens on import)
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        for ($row = 2; $row <= $lastRow; $row++) {
            $validation = $sheet->getCell("D{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);

            // Use direct reference to Categories sheet (simpler and more reliable)
            if (! empty($allCategoryNames)) {
                $formula = sprintf('Categories!A2:A%d', $categoryEndRow);
            } else {
                $formula = '""';
            }

            $validation->setFormula1($formula);
        }

        // Auto-fill Type (Column B) based on selected Category (Column D)
        // Use formula that looks up type from Categories sheet
        // Users can still manually override by deleting formula and typing
        for ($row = 2; $row <= $lastRow; $row++) {
            if (! empty($allCategoryNames)) {
                // Formula: =IF(D2="","",VLOOKUP(D2,Categories!A2:B{endRow},2,FALSE))
                $typeFormula = sprintf('=IF(D%d="","",VLOOKUP(D%d,Categories!A2:B%d,2,FALSE))', $row, $row, $categoryEndRow);
                $sheet->setCellValue("B{$row}", $typeFormula);

                // Add custom validation to ensure type is in allowed list (allows manual override)
                $validation = $sheet->getCell("A{$row}")->getDataValidation();
                $validation->setType(DataValidation::TYPE_CUSTOM);
                $validation->setErrorStyle(DataValidation::STYLE_WARNING);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setErrorTitle(__('Invalid Type'));
                $validation->setError(__('Type must be one of: :types', ['types' => $typeValues]));
                // Custom formula: check if value is empty or in allowed types list
                $typeArray = '{"'.str_replace(',', '","', $typeValues).'"}';
                $validation->setFormula1(sprintf('OR(B%d="",COUNTIF(%s,B%d)>0)', $row, $typeArray, $row));
            } else {
                // If no categories, just use dropdown
                $validation = $sheet->getCell("B{$row}")->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1('"'.$typeValues.'"');
            }
        }

        // Add note/instruction
        $instructionRow = $lastRow + 2;
        $sheet->setCellValue("A{$instructionRow}", __('Note: Transaction ID is optional. Type will be automatically set when you select a Category. You can manually change it if needed.'));
        $sheet->mergeCells("A{$instructionRow}:H{$instructionRow}");
        $sheet->getStyle("A{$instructionRow}")->getFont()->setItalic(true);
        $sheet->getStyle("A{$instructionRow}")->getFont()->getColor()->setARGB('FF808080');
        $sheet->getStyle("A{$instructionRow}")->getAlignment()->setWrapText(true);

        // Client dropdown (Column E)
        $clientValues = implode(',', array_values($clients));
        if (! empty($clientValues)) {
            for ($row = 2; $row <= $lastRow; $row++) {
                $validation = $sheet->getCell("E{$row}")->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1('"'.$clientValues.'"');
            }
        }

        // Status dropdown (Column F)
        $statusValues = implode(',', array_keys($statuses));
        for ($row = 2; $row <= $lastRow; $row++) {
            $validation = $sheet->getCell("F{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"'.$statusValues.'"');
        }

        // Date format validation (Column G)
        for ($row = 2; $row <= $lastRow; $row++) {
            $sheet->getStyle("G{$row}")->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDD);
        }

        $filename = 'transaction_import_sample_'.Str::slug($account->name).'.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import transactions from Excel file.
     */
    public function importTransactions(ImportTransactionsRequest $request, Account $account): RedirectResponse
    {
        $this->authorize('create', \App\Models\Transaction::class);

        try {
            $result = $this->accountService->importTransactionsFromExcel($account, $request->file('csv_file'));

            $message = __('Successfully imported :success transaction(s).', ['success' => $result['success']]);
            if ($result['errors'] > 0) {
                $message .= ' '.__(':errors error(s) encountered.', ['errors' => $result['errors']]);
                if (! empty($result['error_messages'])) {
                    // Store error messages in session for display
                    $request->session()->flash('import_errors', array_slice($result['error_messages'], 0, 10)); // Limit to first 10 errors
                }
            }

            return redirect()
                ->route('accounts.show', $account)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->route('accounts.show', $account)
                ->with('error', __('Failed to import transactions: :message', ['message' => $e->getMessage()]));
        }
    }
}
