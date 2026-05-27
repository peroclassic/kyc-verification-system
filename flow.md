# KYC Verification Flow

1. User submits identity details
2. System sends request to Smile Identity API
3. Smile Identity processes verification
4. Webhook is triggered with verification result
5. Backend validates webhook signature
6. System stores verification result securely
7. Response is returned to the application