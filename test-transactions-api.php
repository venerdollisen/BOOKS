#!/usr/bin/env php
<?php

/**
 * Transaction API Testing Script
 * Tests the Transaction CRUD endpoints
 */

$baseUrl = 'http://localhost:8000/api';

// Helper function to make API requests
function makeRequest($method, $endpoint, $data = null, $token = null) {
    global $baseUrl;
    
    $url = $baseUrl . $endpoint;
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        ...$token ? ["Authorization: Bearer $token"] : [],
    ]);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true),
    ];
}

echo "=== Transaction API Testing ===\n\n";

// 1. Register/Login
echo "1. Testing Authentication...\n";
$registerResponse = makeRequest('POST', '/auth/register', [
    'name' => 'Test User ' . uniqid(),
    'email' => 'test' . uniqid() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
]);

if ($registerResponse['code'] === 201) {
    echo "   ✓ Registration successful\n";
    $token = $registerResponse['body']['token'];
} else {
    echo "   ✗ Registration failed\n";
    print_r($registerResponse['body']);
    exit(1);
}

// 2. Fetch accounts
echo "\n2. Fetching accounts...\n";
$accountsResponse = makeRequest('GET', '/accounts', null, $token);
if ($accountsResponse['code'] === 200 && !empty($accountsResponse['body']['data'])) {
    echo "   ✓ Found " . count($accountsResponse['body']['data']) . " accounts\n";
    $accounts = $accountsResponse['body']['data'];
} else {
    echo "   ✗ Failed to fetch accounts\n";
    exit(1);
}

// 3. Create a new transaction
echo "\n3. Creating a transaction...\n";
$newTransaction = [
    'reference' => 'TEST-' . uniqid(),
    'description' => 'Test transaction',
    'transaction_date' => date('Y-m-d'),
    'type' => 'payment',
    'status' => 'draft',
    'amount' => 500.00,
    'notes' => 'This is a test',
    'items' => [
        [
            'account_id' => $accounts[0]['id'],
            'type' => 'debit',
            'amount' => 250.00,
            'description' => 'Expense item 1',
        ],
        [
            'account_id' => $accounts[1]['id'],
            'type' => 'debit',
            'amount' => 250.00,
            'description' => 'Expense item 2',
        ],
        [
            'account_id' => $accounts[2]['id'],
            'type' => 'credit',
            'amount' => 500.00,
            'description' => 'Credit from bank',
        ],
    ],
];

$createResponse = makeRequest('POST', '/transactions', $newTransaction, $token);
if ($createResponse['code'] === 201) {
    echo "   ✓ Transaction created: " . $createResponse['body']['data']['reference'] . "\n";
    $transactionId = $createResponse['body']['data']['id'];
} else {
    echo "   ✗ Failed to create transaction\n";
    print_r($createResponse['body']);
    exit(1);
}

// 4. Fetch transactions with pagination
echo "\n4. Fetching transactions (paginated)...\n";
$listResponse = makeRequest('GET', '/transactions?page=1&per_page=10', null, $token);
if ($listResponse['code'] === 200) {
    echo "   ✓ Retrieved " . count($listResponse['body']['data']) . " transactions\n";
    echo "   ✓ Total: " . $listResponse['body']['pagination']['total'] . "\n";
    echo "   ✓ Per Page: " . $listResponse['body']['pagination']['per_page'] . "\n";
} else {
    echo "   ✗ Failed to fetch transactions\n";
    exit(1);
}

// 5. Get single transaction
echo "\n5. Fetching single transaction...\n";
$getResponse = makeRequest('GET', "/transactions/$transactionId", null, $token);
if ($getResponse['code'] === 200) {
    echo "   ✓ Retrieved transaction: " . $getResponse['body']['data']['reference'] . "\n";
    echo "   ✓ Has " . count($getResponse['body']['data']['items']) . " line items\n";
} else {
    echo "   ✗ Failed to fetch transaction\n";
    exit(1);
}

// 6. Update transaction
echo "\n6. Updating transaction...\n";
$updateResponse = makeRequest('PUT', "/transactions/$transactionId", [
    'description' => 'Updated description',
    'status' => 'pending',
], $token);

if ($updateResponse['code'] === 200) {
    echo "   ✓ Transaction updated\n";
} else {
    echo "   ✗ Failed to update transaction\n";
    print_r($updateResponse['body']);
}

// 7. Test filtering
echo "\n7. Testing transaction filters...\n";
$filterResponse = makeRequest('GET', '/transactions?status=draft&type=payment&per_page=10', null, $token);
if ($filterResponse['code'] === 200) {
    echo "   ✓ Filtering works - found " . count($filterResponse['body']['data']) . " matching transactions\n";
} else {
    echo "   ✗ Failed to filter transactions\n";
}

// 8. Test sorting
echo "\n8. Testing transaction sorting...\n";
$sortResponse = makeRequest('GET', '/transactions?sort_by=amount&sort_order=desc', null, $token);
if ($sortResponse['code'] === 200) {
    echo "   ✓ Sorting works\n";
} else {
    echo "   ✗ Failed to sort transactions\n";
}

// 9. Approve transaction (change from pending)
echo "\n9. Approving transaction...\n";
$approveResponse = makeRequest('POST', "/transactions/$transactionId/approve", null, $token);
if ($approveResponse['code'] === 200) {
    echo "   ✓ Transaction approved\n";
} else {
    echo "   ✗ Failed to approve transaction\n";
    print_r($approveResponse['body']);
}

// 10. Create and reject a transaction
echo "\n10. Testing rejection workflow...\n";
$rejectTxn = [
    'reference' => 'REJECT-' . uniqid(),
    'description' => 'Test rejection',
    'transaction_date' => date('Y-m-d'),
    'type' => 'payment',
    'status' => 'pending',
    'amount' => 300.00,
    'items' => [
        [
            'account_id' => $accounts[0]['id'],
            'type' => 'debit',
            'amount' => 300.00,
        ],
        [
            'account_id' => $accounts[1]['id'],
            'type' => 'credit',
            'amount' => 300.00,
        ],
    ],
];

$rejectCreateResponse = makeRequest('POST', '/transactions', $rejectTxn, $token);
if ($rejectCreateResponse['code'] === 201) {
    $rejectTxnId = $rejectCreateResponse['body']['data']['id'];
    $rejectResponse = makeRequest('POST', "/transactions/$rejectTxnId/reject", [
        'reason' => 'Test rejection reason',
    ], $token);
    
    if ($rejectResponse['code'] === 200) {
        echo "   ✓ Transaction rejected successfully\n";
    } else {
        echo "   ✗ Failed to reject transaction\n";
    }
}

// 11. Delete transaction
echo "\n11. Testing transaction deletion...\n";
$deleteTxn = [
    'reference' => 'DELETE-' . uniqid(),
    'description' => 'Test deletion',
    'transaction_date' => date('Y-m-d'),
    'type' => 'payment',
    'status' => 'draft',
    'amount' => 200.00,
    'items' => [
        [
            'account_id' => $accounts[0]['id'],
            'type' => 'debit',
            'amount' => 200.00,
        ],
        [
            'account_id' => $accounts[1]['id'],
            'type' => 'credit',
            'amount' => 200.00,
        ],
    ],
];

$deleteCreateResponse = makeRequest('POST', '/transactions', $deleteTxn, $token);
if ($deleteCreateResponse['code'] === 201) {
    $deleteTxnId = $deleteCreateResponse['body']['data']['id'];
    $deleteResponse = makeRequest('DELETE', "/transactions/$deleteTxnId", null, $token);
    
    if ($deleteResponse['code'] === 200) {
        echo "   ✓ Transaction deleted successfully\n";
    } else {
        echo "   ✗ Failed to delete transaction\n";
    }
}

echo "\n=== All Tests Completed Successfully ===\n";
