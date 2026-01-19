# Transaction API - Quick Reference Guide

## Authentication First
```bash
# Register a new user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Your Name",
    "email": "your@email.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Response includes token:
# {"success": true, "token": "1|abc...xyz..."}

# Use this token in all subsequent requests as Bearer token
```

## List Transactions

### Basic List
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/transactions
```

### With Pagination
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  'http://localhost:8000/api/transactions?page=1&per_page=20'
```

### With Search
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  'http://localhost:8000/api/transactions?search=REC-2025'
```

### With Filters
```bash
# Filter by status
curl -H "Authorization: Bearer YOUR_TOKEN" \
  'http://localhost:8000/api/transactions?status=pending'

# Filter by type
curl -H "Authorization: Bearer YOUR_TOKEN" \
  'http://localhost:8000/api/transactions?type=payment'

# Filter by date range
curl -H "Authorization: Bearer YOUR_TOKEN" \
  'http://localhost:8000/api/transactions?start_date=2025-01-01&end_date=2025-01-31'

# Multiple filters combined
curl -H "Authorization: Bearer YOUR_TOKEN" \
  'http://localhost:8000/api/transactions?type=payment&status=approved&per_page=50'
```

### With Sorting
```bash
# Sort by amount descending
curl -H "Authorization: Bearer YOUR_TOKEN" \
  'http://localhost:8000/api/transactions?sort_by=amount&sort_order=desc'

# Sort by date ascending
curl -H "Authorization: Bearer YOUR_TOKEN" \
  'http://localhost:8000/api/transactions?sort_by=transaction_date&sort_order=asc'
```

## Create Transaction

### Basic Receipt (Income)
```bash
curl -X POST http://localhost:8000/api/transactions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "REC-2025-001",
    "description": "Payment from client",
    "transaction_date": "2025-01-20",
    "type": "receipt",
    "status": "draft",
    "amount": 5000,
    "notes": "Client ABC Corp",
    "items": [
      {
        "account_id": 1,
        "type": "debit",
        "amount": 5000,
        "description": "Bank deposit"
      },
      {
        "account_id": 10,
        "type": "credit",
        "amount": 5000,
        "description": "Service income"
      }
    ]
  }'
```

### Basic Payment (Expense)
```bash
curl -X POST http://localhost:8000/api/transactions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "PAY-2025-001",
    "description": "Office supplies",
    "transaction_date": "2025-01-20",
    "type": "payment",
    "status": "draft",
    "amount": 250,
    "items": [
      {
        "account_id": 20,
        "type": "debit",
        "amount": 250,
        "description": "Office supplies expense"
      },
      {
        "account_id": 1,
        "type": "credit",
        "amount": 250,
        "description": "Bank payment"
      }
    ]
  }'
```

### Journal Entry (Multiple Items)
```bash
curl -X POST http://localhost:8000/api/transactions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "JNL-2025-001",
    "description": "Month-end accrual",
    "transaction_date": "2025-01-31",
    "type": "journal",
    "status": "draft",
    "amount": 3000,
    "items": [
      {
        "account_id": 2,
        "type": "debit",
        "amount": 1500,
        "description": "Accounts receivable"
      },
      {
        "account_id": 3,
        "type": "debit",
        "amount": 1500,
        "description": "Prepaid expense"
      },
      {
        "account_id": 10,
        "type": "credit",
        "amount": 3000,
        "description": "Accrued income"
      }
    ]
  }'
```

## Get Single Transaction
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/transactions/1
```

## Update Transaction
```bash
curl -X PUT http://localhost:8000/api/transactions/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Updated description",
    "status": "pending"
  }'
```

## Approve Transaction
```bash
curl -X POST http://localhost:8000/api/transactions/1/approve \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Reject Transaction
```bash
curl -X POST http://localhost:8000/api/transactions/1/reject \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Duplicate transaction detected"
  }'
```

## Delete Transaction
```bash
curl -X DELETE http://localhost:8000/api/transactions/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Response Format

### Successful List Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "reference": "REC-2025-001",
      "description": "Payment from client",
      "transaction_date": "2025-01-20",
      "type": "receipt",
      "status": "draft",
      "amount": "5000.00",
      "notes": "Client ABC Corp",
      "created_at": "2025-01-20T10:00:00Z",
      "updated_at": "2025-01-20T10:00:00Z",
      "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com"
      }
    }
  ],
  "pagination": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5,
    "from": 1,
    "to": 20
  }
}
```

### Successful Create Response
```json
{
  "success": true,
  "message": "Transaction created successfully",
  "data": {
    "id": 1,
    "reference": "REC-2025-001",
    "type": "receipt",
    "status": "draft",
    "amount": "5000.00",
    "transaction_date": "2025-01-20",
    "created_at": "2025-01-20T10:00:00Z",
    "items": [
      {
        "id": 1,
        "transaction_id": 1,
        "account_id": 1,
        "type": "debit",
        "amount": "5000.00",
        "description": "Bank deposit"
      },
      {
        "id": 2,
        "transaction_id": 1,
        "account_id": 10,
        "type": "credit",
        "amount": "5000.00",
        "description": "Service income"
      }
    ]
  }
}
```

### Error Response (Unbalanced)
```json
{
  "success": false,
  "message": "Debits must equal credits. Currently: Debits 5000, Credits 4500"
}
```

### Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "reference": ["The reference has already been taken"],
    "items": ["The items field is required"]
  }
}
```

## Transaction Status Workflow

```
DRAFT → (submit) → PENDING → (approve) → APPROVED
  ↓                  ↓
  └────(delete)      └──(reject)→ REJECTED
                                      ↓
                                   (delete)
```

- **DRAFT**: Editable, deletable, can transition to PENDING
- **PENDING**: Non-editable, awaiting approval, can transition to APPROVED or REJECTED
- **APPROVED**: Finalized, read-only
- **REJECTED**: Non-editable, deletable, shows rejection reason

## Filter Values

### Type Filter
- `receipt` - Income transactions
- `payment` - Expense transactions
- `journal` - Journal entries
- `transfer` - Inter-account transfers

### Status Filter
- `draft` - Work in progress
- `pending` - Awaiting approval
- `approved` - Approved and final
- `rejected` - Rejected with reason

### Sort Columns
- `reference` - Alphabetical by reference
- `type` - By transaction type
- `status` - By approval status
- `amount` - By transaction amount
- `transaction_date` - By date (default)
- `created_at` - By creation date

## Common Errors

### 401 Unauthorized
- Missing or invalid Bearer token
- Token has expired

### 403 Forbidden
- Trying to edit non-draft transaction
- Trying to delete approved transaction
- Trying to approve non-pending transaction

### 422 Unprocessable Entity
- Validation error (check `errors` field)
- Debits don't equal credits
- Missing required fields
- Invalid enum values

### 404 Not Found
- Transaction ID doesn't exist
- Wrong API endpoint

## Best Practices

1. Always include Authorization Bearer token
2. Use `start_date` and `end_date` to limit large queries
3. Use pagination to avoid huge responses
4. Validate debits equal credits before submitting
5. Use transaction references for idempotency
6. Store transaction IDs for later updates
7. Check status before attempting operations
8. Use search for quick transaction lookup
9. Monitor pagination `total` to understand dataset size
10. Use `sort_order: desc` for recent transactions first
