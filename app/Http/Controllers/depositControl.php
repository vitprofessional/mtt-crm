<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Services\MatchTraderApiService;

class depositControl extends Controller
{
    protected $apiService;

    public function __construct(MatchTraderApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function depositView(){
        // Try to fetch deposits from API first
        $apiResponse = $this->apiService->getDeposits();
        
        if ($apiResponse['success']) {
            $deposits = $apiResponse['data'];
            $count = count($deposits);
            $total = $apiResponse['total'] ?? $count;
            $successMessage = "Successfully loaded {$count} deposits from Match Trader API (Total available: {$total})";
            
            return view('deposito.deposit', compact('deposits'))->with('success', $successMessage);
        } else {
            // If API fails, try to fall back to database as backup
            try {
                $deposits = Deposit::orderBy('created_at', 'desc')->get();
                $warningMessage = 'API connection failed. Showing local database data as fallback. API Error: ' . ($apiResponse['message'] ?? 'Unknown error');
                
                return view('deposito.deposit', compact('deposits'))->with('warning', $warningMessage);
            } catch (\Exception $e) {
                // Both API and database failed
                $deposits = collect(); // Empty collection
                $errorMessage = 'Both API and database failed. API: ' . ($apiResponse['message'] ?? 'Unknown error') . '. DB: ' . $e->getMessage();
                
                return view('deposito.deposit', compact('deposits'))->with('error', $errorMessage);
            }
        }
    }
    
    public function depositadd(){
        return view('deposito.addDeposit');
    }
    
    public function detailesDeposit($id = null){
        if($id) {
            // Fetch deposit details from API
            $apiResponse = $this->apiService->getDeposit($id);
            
            if ($apiResponse['success']) {
                $deposit = $apiResponse['data'];
                return view('deposito.dipositDetailes', compact('deposit'));
            } else {
                return redirect()->route('depositView')->with('error', 'Deposit not found');
            }
        }
        return view('deposito.dipositDetailes');
    }

    public function DipoPaymentGte(){
        return view('deposito.bankPayment');
    }

    // Method to store new deposit
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'trading_account' => 'required|string',
            'email' => 'required|email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'status' => 'string',
            'payment_gateway' => 'required|string',
            'payment_id' => 'nullable|string',
            'last_contact' => 'nullable|date',
            'offer' => 'nullable|string',
            'crypto_address' => 'nullable|string',
            'reference_code' => 'nullable|string',
            'lead_source' => 'nullable|string',
            'account_manager' => 'nullable|string'
        ]);

        // Create deposit via API
        $apiResponse = $this->apiService->createDeposit($validatedData);
        
        if ($apiResponse['success']) {
            return redirect()->route('depositView')->with('success', 'Deposit created successfully!');
        } else {
            return redirect()->back()->with('error', $apiResponse['message'])->withInput();
        }
    }

    // Method to search deposits
    public function search(Request $request)
    {
        $filters = [];
        
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        
        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->date_from;
        }
        
        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->date_to;
        }

        // Fetch filtered deposits from API
        $apiResponse = $this->apiService->getDeposits($filters);
        
        if ($apiResponse['success']) {
            $deposits = $apiResponse['data'];
            return view('deposito.deposit', compact('deposits'));
        } else {
            $deposits = collect(); // Empty collection
            $errorMessage = $apiResponse['message'] ?? 'Failed to search deposits';
            
            return view('deposito.deposit', compact('deposits'))->with('error', $errorMessage);
        }
    }

    // Method to accept a deposit
    public function acceptDeposit($id)
    {
        try {
            $result = $this->apiService->acceptDeposit($id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Deposit accepted successfully!',
                    'data' => $result['data'] ?? null
                ]);
            } else {
                // Check if it's an API limitation issue
                if (strpos($result['message'], 'not available in this API version') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Accept/Reject functionality is not available in the demo API version. This feature would work in the production API.',
                        'api_limitation' => true
                    ], 501); // 501 Not Implemented
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to accept deposit'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error accepting deposit: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method to reject a deposit
    public function rejectDeposit(Request $request, $id)
    {
        try {
            $reason = $request->input('reason', '');
            $result = $this->apiService->rejectDeposit($id, $reason);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Deposit rejected successfully!',
                    'data' => $result['data'] ?? null
                ]);
            } else {
                // Check if it's an API limitation issue
                if (strpos($result['message'], 'not available in this API version') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Accept/Reject functionality is not available in the demo API version. This feature would work in the production API.',
                        'api_limitation' => true
                    ], 501); // 501 Not Implemented
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to reject deposit'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting deposit: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method to update deposit status
    public function updateStatus(Request $request, $id)
    {
        try {
            $status = $request->input('status');
            $additionalData = $request->except(['status', '_token']);
            
            $result = $this->apiService->updateDepositStatus($id, $status, $additionalData);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Deposit status updated successfully!',
                    'data' => $result['data'] ?? null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to update deposit status'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating deposit status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method to test API connection
    public function testApi()
    {
        $result = $this->apiService->testConnection();
        
        return response()->json($result);
    }
}
