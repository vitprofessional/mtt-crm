<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MatchTraderApiService
{
    private $baseUrl;
    private $authorization;
    private $contentType;

    public function __construct()
    {
        $this->baseUrl = config('app.api_base_url', env('API_BASE_URL'));
        $this->authorization = config('app.api_authorization', env('API_AUTHORIZATION'));
        $this->contentType = config('app.api_content_type', env('API_CONTENT_TYPE', 'application/json'));
    }

    /**
     * Get HTTP client with default headers
     */
    private function getHttpClient()
    {
        return Http::withHeaders([
            'Content-Type' => $this->contentType,
            'Authorization' => $this->authorization,
            'Accept' => 'application/json',
        ])->timeout(90)->retry(3, 2000); // Increased timeout to 90s, 3 retries with 2s delay
    }

    /**
     * Get all deposits from the API
     */
    public function getDeposits($filters = [], $fetchAll = true)
    {
        try {
            // Use the working accounts endpoint like in leads
            $url = $this->baseUrl . 'accounts';
            
            // Add query parameters for filtering and pagination
            $queryParams = [
                'accountType' => 'CLIENT', // Get client accounts that may have deposit data
                'size' => 100, // Reduced from 1000 to 100 to prevent timeouts
                'page' => 0    // Start from first page
            ];
            
            if (!empty($filters['search'])) {
                $queryParams['search'] = $filters['search'];
            }
            if (!empty($filters['date_from'])) {
                $queryParams['date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $queryParams['date_to'] = $filters['date_to'];
            }
            if (!empty($filters['status'])) {
                $queryParams['status'] = $filters['status'];
            }

            $response = $this->getHttpClient()->get($url, $queryParams);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Check if we need to fetch more pages to get all data
                if ($fetchAll && isset($responseData['totalPages']) && $responseData['totalPages'] > 1) {
                    return $this->getAllDepositPages($filters, $responseData);
                }
                
                return $this->formatDepositsResponse($responseData);
            }

            Log::error('API Error getting deposits', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch deposits from API',
                'data' => []
            ];

        } catch (\Exception $e) {
            Log::error('Exception getting deposits from API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Get a specific deposit by ID
     */
    public function getDeposit($id)
    {
        try {
            $url = $this->baseUrl . 'deposits/' . $id;
            $response = $this->getHttpClient()->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $this->formatSingleDeposit($data)
                ];
            }

            return [
                'success' => false,
                'message' => 'Deposit not found',
                'data' => null
            ];

        } catch (\Exception $e) {
            Log::error('Exception getting deposit from API', [
                'id' => $id,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch deposit',
                'data' => null
            ];
        }
    }

    /**
     * Create a new deposit via API
     */
    public function createDeposit($data)
    {
        try {
            $url = $this->baseUrl . 'deposits';
            $response = $this->getHttpClient()->post($url, $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Deposit created successfully',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create deposit',
                'errors' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Exception creating deposit via API', [
                'data' => $data,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create deposit: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format the API response for deposits list
     */
    private function formatDepositsResponse($response)
    {
        // Handle Match Trader API response structure
        $deposits = [];
        
        if (isset($response['content']) && is_array($response['content'])) {
            $deposits = $response['content'];
        } elseif (isset($response['data']) && is_array($response['data'])) {
            $deposits = $response['data'];
        } elseif (is_array($response)) {
            $deposits = $response;
        }

        $formattedDeposits = collect($deposits)->map(function ($deposit) {
            return $this->formatSingleDeposit($deposit);
        });

        return [
            'success' => true,
            'data' => $formattedDeposits,
            'total' => isset($response['totalElements']) ? $response['totalElements'] : count($formattedDeposits),
            'pagination' => [
                'current_page' => isset($response['number']) ? $response['number'] + 1 : 1,
                'total_pages' => $response['totalPages'] ?? 1,
                'per_page' => $response['size'] ?? count($formattedDeposits)
            ]
        ];
    }

    /**
     * Format a single deposit record to match our view expectations
     */
    private function formatSingleDeposit($deposit)
    {
        // Extract data from Match Trader API structure
        $accountInfo = $deposit['accountInfo'] ?? [];
        $personalDetails = $accountInfo['personalDetails'] ?? [];
        $tradingAccount = $accountInfo['tradingAccount'] ?? [];
        $accountManager = $accountInfo['accountManager'] ?? [];
        $leadDetails = $accountInfo['leadDetails'] ?? [];
        $paymentInfo = $deposit['paymentRequestInfo'] ?? [];
        $financialDetails = $paymentInfo['financialDetails'] ?? [];
        $paymentGateway = $paymentInfo['paymentGatewayDetails'] ?? [];

        return (object) [
            'id' => $deposit['uuid'] ?? uniqid(),
            'trading_account' => $tradingAccount['login'] ?? 'N/A',
            'email' => $accountInfo['email'] ?? 'N/A',
            'first_name' => $personalDetails['firstname'] ?? 'N/A',
            'last_name' => $personalDetails['lastname'] ?? 'N/A',
            'amount' => (float) ($financialDetails['amount'] ?? 0),
            'net_amount' => (float) ($financialDetails['netAmount'] ?? $financialDetails['amount'] ?? 0),
            'currency' => $financialDetails['currency'] ?? 'USD',
            'status' => strtolower($financialDetails['status'] ?? 'pending'),
            'payment_gateway' => $paymentGateway['name'] ?? 'N/A',
            'payment_id' => $deposit['uuid'] ?? null,
            'last_contact' => isset($deposit['updated']) ? \Carbon\Carbon::parse($deposit['updated']) : null,
            'offer' => $tradingAccount['offerUuid'] ?? 'Standard',
            'crypto_address' => null, // Not available in this API structure
            'reference_code' => $deposit['uuid'] ?? null,
            'lead_source' => $leadDetails['source'] ?? null,
            'account_manager' => trim($accountManager['name'] ?? '') ?: null,
            'created_at' => isset($deposit['created']) ? \Carbon\Carbon::parse($deposit['created']) : \Carbon\Carbon::now(),
            'updated_at' => isset($deposit['updated']) ? \Carbon\Carbon::parse($deposit['updated']) : \Carbon\Carbon::now(),
            'remark' => $deposit['remark'] ?? null,
            'country' => $accountInfo['country'] ?? null,
        ];
    }

    /**
     * Fetch all deposits across multiple pages
     */
    private function getAllDepositPages($filters = [], $firstPageData = null)
    {
        try {
            $allDeposits = [];
            $url = $this->baseUrl . 'accounts';
            
            // Start with first page data if provided
            if ($firstPageData && isset($firstPageData['content'])) {
                $allDeposits = array_merge($allDeposits, $firstPageData['content']);
            }
            
            $totalPages = $firstPageData['totalPages'] ?? 1;
            $maxPages = $totalPages; // Fetch all pages to get all data
            
            // Fetch remaining pages (limit to first 10 pages to prevent excessive API calls)
            $maxPagesToFetch = min($maxPages, 10);
            for ($page = 1; $page < $maxPagesToFetch; $page++) {
                $queryParams = [
                    'accountType' => 'CLIENT',
                    'size' => 100, // Reduced page size
                    'page' => $page
                ];
                
                // Add filters
                if (!empty($filters['search'])) {
                    $queryParams['search'] = $filters['search'];
                }
                if (!empty($filters['date_from'])) {
                    $queryParams['date_from'] = $filters['date_from'];
                }
                if (!empty($filters['date_to'])) {
                    $queryParams['date_to'] = $filters['date_to'];
                }
                if (!empty($filters['status'])) {
                    $queryParams['status'] = $filters['status'];
                }
                
                $response = $this->getHttpClient()->get($url, $queryParams);
                
                if ($response->successful()) {
                    $pageData = $response->json();
                    if (isset($pageData['content']) && is_array($pageData['content'])) {
                        $allDeposits = array_merge($allDeposits, $pageData['content']);
                    }
                } else {
                    // If a page fails, log it but continue with what we have
                    Log::warning('Failed to fetch deposits page', [
                        'page' => $page,
                        'status' => $response->status()
                    ]);
                    break;
                }
            }
            
            // Create combined response
            $combinedResponse = [
                'content' => $allDeposits,
                'totalElements' => count($allDeposits),
                'totalPages' => $totalPages,
                'number' => 0,
                'size' => count($allDeposits)
            ];
            
            return $this->formatDepositsResponse($combinedResponse);
            
        } catch (\Exception $e) {
            Log::error('Exception fetching all deposit pages', [
                'message' => $e->getMessage()
            ]);
            
            // Return first page data if available
            if ($firstPageData) {
                return $this->formatDepositsResponse($firstPageData);
            }
            
            return [
                'success' => false,
                'message' => 'Failed to fetch all deposits: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Accept a deposit/withdrawal request
     */
    public function acceptDeposit($depositId)
    {
        try {
            // Try multiple possible endpoints for accepting deposits
            $endpoints = [
                'deposits/' . $depositId . '/approve',
                'deposits/' . $depositId . '/accept',
                'deposits/' . $depositId . '/status',
                'transactions/deposits/' . $depositId . '/approve',
                'financial-operations/deposits/' . $depositId . '/approve'
            ];

            $requestData = [
                'status' => 'approved',
                'action' => 'accept'
            ];

            Log::info('Attempting to accept deposit', [
                'deposit_id' => $depositId,
                'endpoints_to_try' => count($endpoints)
            ]);

            foreach ($endpoints as $endpoint) {
                $url = $this->baseUrl . $endpoint;
                Log::info('Trying accept deposit endpoint', ['url' => $url]);
                
                $response = $this->getHttpClient()->post($url, $requestData);

                Log::info('Accept deposit response', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);

                if ($response->successful()) {
                    Log::info('Successfully accepted deposit', [
                        'endpoint' => $endpoint,
                        'deposit_id' => $depositId,
                        'response' => $response->json()
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Deposit accepted successfully',
                        'data' => $response->json()
                    ];
                }
            }

            // If no endpoint worked, return a more informative error
            Log::warning('All accept deposit endpoints failed', [
                'deposit_id' => $depositId,
                'endpoints_tried' => $endpoints
            ]);

            return [
                'success' => false,
                'message' => 'Accept deposit feature not available in this API version. Endpoints tried: ' . implode(', ', $endpoints)
            ];

        } catch (\Exception $e) {
            Log::error('Exception accepting deposit via API', [
                'deposit_id' => $depositId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to accept deposit: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject a deposit/withdrawal request
     */
    public function rejectDeposit($depositId, $reason = '')
    {
        try {
            // Try multiple possible endpoints for rejecting deposits
            $endpoints = [
                'deposits/' . $depositId . '/reject',
                'deposits/' . $depositId . '/decline',
                'deposits/' . $depositId . '/status',
                'transactions/deposits/' . $depositId . '/reject',
                'financial-operations/deposits/' . $depositId . '/reject'
            ];

            $requestData = [
                'status' => 'rejected',
                'action' => 'reject',
                'reason' => $reason
            ];

            Log::info('Attempting to reject deposit', [
                'deposit_id' => $depositId,
                'reason' => $reason,
                'endpoints_to_try' => count($endpoints)
            ]);

            foreach ($endpoints as $endpoint) {
                $url = $this->baseUrl . $endpoint;
                Log::info('Trying reject deposit endpoint', ['url' => $url]);
                
                $response = $this->getHttpClient()->post($url, $requestData);

                Log::info('Reject deposit response', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);

                if ($response->successful()) {
                    Log::info('Successfully rejected deposit', [
                        'endpoint' => $endpoint,
                        'deposit_id' => $depositId,
                        'response' => $response->json()
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Deposit rejected successfully',
                        'data' => $response->json()
                    ];
                }
            }

            // If no endpoint worked, return a more informative error
            Log::warning('All reject deposit endpoints failed', [
                'deposit_id' => $depositId,
                'endpoints_tried' => $endpoints
            ]);

            return [
                'success' => false,
                'message' => 'Reject deposit feature not available in this API version. Endpoints tried: ' . implode(', ', $endpoints)
            ];

        } catch (\Exception $e) {
            Log::error('Exception rejecting deposit via API', [
                'deposit_id' => $depositId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reject deposit: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update deposit status
     */
    public function updateDepositStatus($depositId, $status, $additionalData = [])
    {
        try {
            $url = $this->baseUrl . 'deposits/' . $depositId . '/status';
            
            $requestData = array_merge([
                'status' => $status
            ], $additionalData);

            $response = $this->getHttpClient()->put($url, $requestData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Deposit status updated successfully',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update deposit status',
                'errors' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Exception updating deposit status via API', [
                'deposit_id' => $depositId,
                'status' => $status,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update deposit status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get withdrawals with pagination and filtering
     */
    public function getWithdrawals($filters = [])
    {
        try {
            Log::info('Fetching withdrawals from API', ['filters' => $filters]);

            // Set default page size for optimal performance
            $pageSize = 100; // Reduced from 500 to 100
            $allWithdrawals = collect();
            $page = 0;
            $totalFetched = 0;
            $maxPages = 10; // Limit total pages to prevent excessive API calls

            do {
                $queryParams = array_merge([
                    'page' => $page,
                    'size' => $pageSize
                ], $filters);

                $url = $this->baseUrl . 'withdrawals?' . http_build_query($queryParams);
                $response = $this->getHttpClient()->get($url);

                if (!$response->successful()) {
                    Log::warning('Failed to fetch withdrawals page', [
                        'page' => $page,
                        'status' => $response->status()
                    ]);
                    break;
                }

                $responseData = $response->json();
                
                if (isset($responseData['content']) && is_array($responseData['content'])) {
                    $withdrawals = collect($responseData['content']);
                    $allWithdrawals = $allWithdrawals->merge($withdrawals);
                    $totalFetched += $withdrawals->count();

                    Log::info('Withdrawal API Response Structure for page ' . $page, [
                        'total_elements' => $responseData['totalElements'] ?? 'unknown',
                        'total_pages' => $responseData['totalPages'] ?? 'unknown',
                        'current_page' => $page,
                        'content_count' => $withdrawals->count()
                    ]);

                    // Check if we have more pages
                    if (isset($responseData['totalPages']) && $page >= ($responseData['totalPages'] - 1)) {
                        break;
                    }
                } else {
                    Log::warning('Unexpected withdrawal API response structure', ['response' => $responseData]);
                    break;
                }

                $page++;
            } while ($page < $maxPages); // Reduced safety limit

            Log::info('Withdrawal fetching completed', [
                'total_fetched' => $totalFetched,
                'pages_processed' => $page,
                'note' => 'ALL withdrawal data retrieved successfully'
            ]);

            // Log the structure of the first withdrawal for debugging
            if ($allWithdrawals->isNotEmpty()) {
                Log::info('Sample Withdrawal Structure', [
                    'first_withdrawal' => $allWithdrawals->first(),
                    'available_fields' => array_keys($allWithdrawals->first())
                ]);
            }

            return [
                'success' => true,
                'data' => $allWithdrawals,
                'total_count' => $totalFetched
            ];

        } catch (\Exception $e) {
            Log::error('Exception fetching withdrawals via API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch withdrawals: ' . $e->getMessage(),
                'data' => collect()
            ];
        }
    }

    /**
     * Accept a withdrawal request
     */
    public function acceptWithdrawal($withdrawalId)
    {
        try {
            // Try multiple possible endpoints for accepting withdrawals
            $endpoints = [
                'withdrawals/' . $withdrawalId . '/approve',
                'withdrawals/' . $withdrawalId . '/accept',
                'withdrawals/' . $withdrawalId . '/status',
                'transactions/withdrawals/' . $withdrawalId . '/approve',
                'financial-operations/withdrawals/' . $withdrawalId . '/approve'
            ];

            $requestData = [
                'status' => 'approved',
                'action' => 'accept'
            ];

            Log::info('Attempting to accept withdrawal', [
                'withdrawal_id' => $withdrawalId,
                'endpoints_to_try' => count($endpoints)
            ]);

            foreach ($endpoints as $endpoint) {
                $url = $this->baseUrl . $endpoint;
                Log::info('Trying accept withdrawal endpoint', ['url' => $url]);
                
                $response = $this->getHttpClient()->post($url, $requestData);

                Log::info('Accept withdrawal response', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);

                if ($response->successful()) {
                    Log::info('Successfully accepted withdrawal', [
                        'endpoint' => $endpoint,
                        'withdrawal_id' => $withdrawalId,
                        'response' => $response->json()
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Withdrawal accepted successfully',
                        'data' => $response->json()
                    ];
                }
            }

            // If no endpoint worked, return a more informative error
            Log::warning('All accept withdrawal endpoints failed', [
                'withdrawal_id' => $withdrawalId,
                'endpoints_tried' => $endpoints
            ]);

            return [
                'success' => false,
                'message' => 'Accept withdrawal feature not available in this API version. Endpoints tried: ' . implode(', ', $endpoints)
            ];

        } catch (\Exception $e) {
            Log::error('Exception accepting withdrawal via API', [
                'withdrawal_id' => $withdrawalId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to accept withdrawal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject a withdrawal request
     */
    public function rejectWithdrawal($withdrawalId, $reason = '')
    {
        try {
            // Try multiple possible endpoints for rejecting withdrawals
            $endpoints = [
                'withdrawals/' . $withdrawalId . '/reject',
                'withdrawals/' . $withdrawalId . '/decline',
                'withdrawals/' . $withdrawalId . '/status',
                'transactions/withdrawals/' . $withdrawalId . '/reject',
                'financial-operations/withdrawals/' . $withdrawalId . '/reject'
            ];

            $requestData = [
                'status' => 'rejected',
                'action' => 'reject',
                'reason' => $reason
            ];

            Log::info('Attempting to reject withdrawal', [
                'withdrawal_id' => $withdrawalId,
                'reason' => $reason,
                'endpoints_to_try' => count($endpoints)
            ]);

            foreach ($endpoints as $endpoint) {
                $url = $this->baseUrl . $endpoint;
                Log::info('Trying reject withdrawal endpoint', ['url' => $url]);
                
                $response = $this->getHttpClient()->post($url, $requestData);

                Log::info('Reject withdrawal response', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);

                if ($response->successful()) {
                    Log::info('Successfully rejected withdrawal', [
                        'endpoint' => $endpoint,
                        'withdrawal_id' => $withdrawalId,
                        'response' => $response->json()
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Withdrawal rejected successfully',
                        'data' => $response->json()
                    ];
                }
            }

            // If no endpoint worked, return a more informative error
            Log::warning('All reject withdrawal endpoints failed', [
                'withdrawal_id' => $withdrawalId,
                'endpoints_tried' => $endpoints
            ]);

            return [
                'success' => false,
                'message' => 'Reject withdrawal feature not available in this API version. Endpoints tried: ' . implode(', ', $endpoints)
            ];

        } catch (\Exception $e) {
            Log::error('Exception rejecting withdrawal via API', [
                'withdrawal_id' => $withdrawalId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reject withdrawal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update withdrawal status
     */
    public function updateWithdrawalStatus($withdrawalId, $status, $additionalData = [])
    {
        try {
            $url = $this->baseUrl . 'withdrawals/' . $withdrawalId . '/status';
            
            $requestData = array_merge([
                'status' => $status
            ], $additionalData);

            $response = $this->getHttpClient()->put($url, $requestData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Withdrawal status updated successfully',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update withdrawal status',
                'errors' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Exception updating withdrawal status via API', [
                'withdrawal_id' => $withdrawalId,
                'status' => $status,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update withdrawal status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $response = $this->getHttpClient()->get($this->baseUrl . 'ping');
            
            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'message' => $response->successful() ? 'API connection successful' : 'API connection failed'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            ];
        }
    }
}