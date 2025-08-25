# Cook-Off Data Export API

This API provides secure access to export voting data for investigation and analysis purposes.

## Authentication

All API endpoints require authentication using an API key passed in the `X-API-Key` header. API keys are managed through the Filament admin panel.

### Setup

1. **Access the Admin Panel**: Navigate to your Filament admin panel (usually `/admin`)

2. **Create API Key**:
   - Go to "System" → "API Keys"
   - Click "Create API Key"
   - Enter a name and optional description
   - Select specific permissions or leave empty for full access
   - Set an optional expiration date
   - The system will generate a secure API key automatically

3. **Copy the API Key**: After creation, copy the generated key from the notification or table

### API Key Management Features

- **Secure Generation**: Keys are automatically generated with 63-character random strings
- **Permission System**: Granular permissions for each endpoint
- **Expiration Dates**: Optional key expiration for enhanced security
- **Usage Tracking**: Last used timestamps for monitoring
- **Key Regeneration**: Ability to regenerate keys without losing configuration
- **Activity Status**: Enable/disable keys without deletion

### Usage

Include the API key in all requests:
```bash
curl -H "X-API-Key: your-secure-api-key-here" https://your-domain.com/api/export/contests
```

## Endpoints

All endpoints return JSON data with the following structure:
```json
{
  "data": [...],
  "meta": {
    "total": 123,
    "exported_at": "2024-12-17T10:30:00Z"
  }
}
```

### GET /api/export/contests

Export all contests with summary information.

**Response includes:**
- Contest details (id, name, description, rating limits, voting windows)
- Entry and vote counts
- Winning entries
- Voting status

### GET /api/export/votes

Export all votes with detailed rating information.

**Response includes:**
- Vote metadata and summary
- Associated contest information
- All ratings for each vote
- Entry and rating factor details

### GET /api/export/entries

Export all entries with their ratings and performance data.

**Response includes:**
- Entry details
- Average ratings
- Vote counts
- Detailed rating breakdowns

### GET /api/export/vote-ratings

Export raw vote rating data for detailed analysis.

**Response includes:**
- Individual rating records
- Entry and contest associations
- Rating factor details
- Timestamps

### GET /api/export/contest/{contest_id}

Export comprehensive data for a specific contest.

**Response includes:**
- Complete contest details
- All entries with detailed ratings
- All votes and their ratings
- Rating factor definitions
- Statistical summaries

## Security Considerations

- The API key should be kept secure and not shared publicly
- Use HTTPS in production to protect API key transmission
- Rotate API keys regularly
- Monitor API usage through server logs
- Consider IP whitelisting for additional security

## Rate Limiting

Currently no rate limiting is implemented. Consider adding rate limiting middleware for production use.

## Error Responses

- `401 Unauthorized`: Missing or invalid API key
- `404 Not Found`: Contest not found (for single contest endpoint)
- `500 Internal Server Error`: Server error

## Data Investigation

This API is specifically designed for investigating voting data discrepancies. The exported data includes:

1. **Vote integrity**: All vote ratings with timestamps
2. **Contest analysis**: Winner calculations and voting patterns  
3. **Entry performance**: Average ratings and vote distributions
4. **Raw data access**: Complete vote rating records for analysis

## Example Usage

```bash
# Export all contests
curl -H "X-API-Key: your-key" https://your-domain.com/api/export/contests

# Get detailed data for contest ID 1
curl -H "X-API-Key: your-key" https://your-domain.com/api/export/contest/1

# Export all vote ratings for analysis
curl -H "X-API-Key: your-key" https://your-domain.com/api/export/vote-ratings
```
