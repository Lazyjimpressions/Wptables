import os
import google.auth
from googleapiclient.discovery import build
from google_auth_oauthlib.flow import InstalledAppFlow
from google.auth.transport.requests import Request
import logging

logging.basicConfig(level=logging.DEBUG)

# Define the API scope for read-only access to Google Drive
SCOPES = ['https://www.googleapis.com/auth/drive.readonly']

def authenticate_drive():
    creds = None
    logging.debug("Initiating OAuth flow")
    # Check if token.json already exists (this stores your credentials after the first login)
    if os.path.exists('token.json'):
        creds = google.auth.load_credentials_from_file('token.json')
    # If no valid credentials, start OAuth flow
    if not creds or not creds.valid:
        if creds and creds.expired and creds.refresh_token:
            creds.refresh(Request())
        else:
            # Use the Codespaces forwarded URL
            flow = InstalledAppFlow.from_client_secrets_file('client_secret.json', SCOPES)
            creds = flow.run_local_server(port=49441, host='0.0.0.0')  # Use 0.0.0.0 to allow external access
        # Save the credentials for the next time
        with open('token.json', 'w') as token:
            token.write(creds.to_json())
    return build('drive', 'v3', credentials=creds)

def fetch_design_guidelines(file_name='WP-Tables Design Guidelines'):
    service = authenticate_drive()
    # Search for the file by its name
    results = service.files().list(q=f"name='{file_name}'", fields="files(id, name)").execute()
    items = results.get('files', [])

    if not items:
        print(f"No file found with the name: {file_name}")
    else:
        file_id = items[0]['id']
        # Fetch the file content
        request = service.files().get_media(fileId=file_id)
        file_content = request.execute()
        return file_content.decode('utf-8')

if __name__ == '__main__':
    content = fetch_design_guidelines()
    print(content)
