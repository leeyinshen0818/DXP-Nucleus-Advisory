function fetchLeads() {
  var API_URL = 'https://nucleusadvisory.co/wp-json/nucleus/v1/leads';
  var API_KEY = 'vbskdvniaw8ry238';

  try {
    var url = API_URL + '?api_key=' + API_KEY + '&limit=200';

    var response;
    try {
      response = UrlFetchApp.fetch(url, {
        muteHttpExceptions: true,
        followRedirects: true,
        validateHttpsCertificates: true,
        deadline: 20  // ✅ Hard timeout in seconds — prevents runtime kill
      });
    } catch (fetchError) {
      // Network-level failure (DNS, SSL, connection refused, timeout)
      Logger.log('Network error fetching leads: ' + fetchError.message);
      return;
    }

    var statusCode = response.getResponseCode();

    // ✅ Check response size BEFORE reading content text
    var headers = response.getHeaders();
    Logger.log('Response status: ' + statusCode);
    Logger.log('Content-Type: ' + (headers['Content-Type'] || headers['content-type'] || 'unknown'));

    var contentText = response.getContentText();
    Logger.log('Response size: ' + contentText.length + ' characters');

    // ✅ Bail early if response is suspiciously large (HTML error page, etc.)
    if (contentText.length > 500000) {
      Logger.log('Response too large (' + contentText.length + ' chars) — likely not JSON. Aborting.');
      Logger.log('First 500 chars: ' + contentText.substring(0, 500));
      return;
    }

    if (statusCode !== 200) {
      Logger.log('API returned error status: ' + statusCode);
      Logger.log('Response (first 500): ' + contentText.substring(0, 500));
      return;
    }

    var trimmed = contentText.trim();
    if (trimmed.charAt(0) !== '{' && trimmed.charAt(0) !== '[') {
      Logger.log('Not JSON. First 500 chars: ' + trimmed.substring(0, 500));
      return;
    }

    var json;
    try {
      json = JSON.parse(contentText);
    } catch (parseError) {
      Logger.log('JSON parse error: ' + parseError.message);
      Logger.log('First 500: ' + contentText.substring(0, 500));
      return;
    }

    if (!json.leads || json.leads.length === 0) {
      Logger.log('No leads. Keys: ' + Object.keys(json).join(', '));
      return;
    }

    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();

    if (sheet.getLastRow() > 1) {
      sheet.getRange(2, 1, sheet.getLastRow() - 1, 7).clearContent();
    }

    var rows = json.leads.map(function(lead) {
      return [
        lead.id || '',
        lead.name || '',
        lead.email || '',
        lead.company || '',
        lead.phone || '',
        lead.reason || '',
        lead.submitted_at || ''
      ];
    });

    sheet.getRange(2, 1, rows.length, 7).setValues(rows);
    Logger.log('Synced ' + rows.length + ' leads.');

  } catch (e) {
    Logger.log('FATAL: ' + e.message);
    Logger.log('Stack: ' + (e.stack || 'N/A'));
  }
}