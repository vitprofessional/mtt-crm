<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MatchTraderApiService;
use Exception;

class widthdrawControll extends Controller
{
    protected $apiService;

    public function __construct()
    {
        $this->apiService = new MatchTraderApiService();
    }

    public function widthdrawView(){
        try {
            // Fetch withdrawals from API
            $apiResponse = $this->apiService->getWithdrawals();
            
            if ($apiResponse['success']) {
                $withdrawals = $apiResponse['data'];
                
                // Debug logging
                \Log::info('Withdrawal View Debug', [
                    'withdrawals_count' => $withdrawals->count(),
                    'is_collection' => $withdrawals instanceof \Illuminate\Support\Collection,
                    'first_withdrawal' => $withdrawals->first()
                ]);
                
                return view('widthDraw.widthdraw', compact('withdrawals'));
            } else {
                $withdrawals = collect(); // Empty collection
                $errorMessage = $apiResponse['message'] ?? 'Failed to fetch withdrawals';
                
                \Log::warning('Withdrawal View Error', [
                    'error' => $errorMessage,
                    'api_response' => $apiResponse
                ]);
                
                return view('widthDraw.widthdraw', compact('withdrawals'))->with('error', $errorMessage);
            }
        } catch (Exception $e) {
            $withdrawals = collect();
            
            \Log::error('Withdrawal View Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('widthDraw.widthdraw', compact('withdrawals'))->with('error', 'Error fetching withdrawals: ' . $e->getMessage());
        }
    }

    public function widthdrawAdd(){
        return view('widthDraw.addWidthdraw');
    }

    public function widthdrawDetailes(){
        return view('widthDraw.widthdrawDetailes');
    }

    public function widthdrawPaymentGet(){
        return view('widthDraw.bankpayment');
    }

    // Method to search/filter withdrawals
    public function search(Request $request)
    {
        $filters = [];
        
        if ($request->filled('email')) {
            $filters['email'] = $request->email;
        }
        
        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }
        
        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->date_from;
        }
        
        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->date_to;
        }

        // Fetch filtered withdrawals from API
        $apiResponse = $this->apiService->getWithdrawals($filters);
        
        if ($apiResponse['success']) {
            $withdrawals = $apiResponse['data'];
            return view('widthDraw.widthdraw', compact('withdrawals'));
        } else {
            $withdrawals = collect(); // Empty collection
            $errorMessage = $apiResponse['message'] ?? 'Failed to search withdrawals';
            
            return view('widthDraw.widthdraw', compact('withdrawals'))->with('error', $errorMessage);
        }
    }

    // Method to accept a withdrawal
    public function acceptWithdrawal($id)
    {
        try {
            $result = $this->apiService->acceptWithdrawal($id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Withdrawal accepted successfully!',
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
                    'message' => $result['message'] ?? 'Failed to accept withdrawal'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error accepting withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method to reject a withdrawal
    public function rejectWithdrawal(Request $request, $id)
    {
        try {
            $reason = $request->input('reason', '');
            $result = $this->apiService->rejectWithdrawal($id, $reason);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Withdrawal rejected successfully!',
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
                    'message' => $result['message'] ?? 'Failed to reject withdrawal'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method to update withdrawal status
    public function updateStatus(Request $request, $id)
    {
        try {
            $status = $request->input('status');
            $additionalData = $request->except(['status', '_token']);
            
            $result = $this->apiService->updateWithdrawalStatus($id, $status, $additionalData);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Withdrawal status updated successfully!',
                    'data' => $result['data'] ?? null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to update withdrawal status'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating withdrawal status: ' . $e->getMessage()
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
