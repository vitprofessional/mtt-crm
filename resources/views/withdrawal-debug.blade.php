<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawals Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <h2>Withdrawals Debug Page</h2>
        <p>Total Withdrawals: {{ $withdrawals->count() }}</p>
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped border">
                        <thead class="table-dark">
                            <tr>
                                <th class="bg-dark text-white">ID</th>
                                <th class="bg-dark text-white">Email</th>
                                <th class="bg-dark text-white">Name</th>
                                <th class="bg-dark text-white">Amount</th>
                                <th class="bg-dark text-white">Currency</th>
                                <th class="bg-dark text-white">Status</th>
                                <th class="bg-dark text-white">Created</th>
                                <th class="bg-dark text-white">Gateway</th>
                                <th class="bg-dark text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($withdrawals->isEmpty())
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <p>No withdrawals found</p>
                                    </td>
                                </tr>
                            @else
                                @foreach($withdrawals->take(5) as $withdrawal)
                                <tr>
                                    <td>{{ substr($withdrawal['uuid'] ?? 'W-' . rand(1000, 9999), 0, 8) }}</td>
                                    <td>{{ $withdrawal['accountInfo']['email'] ?? 'N/A' }}</td>
                                    <td>{{ $withdrawal['accountInfo']['personalDetails']['firstname'] ?? 'N/A' }} {{ $withdrawal['accountInfo']['personalDetails']['lastname'] ?? '' }}</td>
                                    <td>${{ number_format($withdrawal['paymentRequestInfo']['financialDetails']['amount'] ?? 0, 2) }}</td>
                                    <td>{{ $withdrawal['paymentRequestInfo']['financialDetails']['currency'] ?? 'USD' }}</td>
                                    <td>{{ $withdrawal['paymentRequestInfo']['financialDetails']['status'] ?? 'pending' }}</td>
                                    <td>{{ isset($withdrawal['created']) ? \Carbon\Carbon::parse($withdrawal['created'])->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $withdrawal['paymentRequestInfo']['paymentGatewayDetails']['name'] ?? 'Bank' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-success">Accept</button>
                                        <button class="btn btn-sm btn-danger">Reject</button>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <h4>Debug Info:</h4>
            <pre>{{ json_encode(['count' => $withdrawals->count(), 'first' => $withdrawals->first()], JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</body>
</html>