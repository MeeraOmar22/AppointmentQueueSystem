<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">WhatsApp Phone Number Testing</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Test your phone number formatting and WhatsApp API integration</p>

                    <form id="phoneTestForm">
                        <div class="mb-3">
                            <label for="testPhone" class="form-label">Phone Number to Test</label>
                            <input type="tel" id="testPhone" class="form-control" placeholder="e.g., 01155577037 or +601155577037" value="">
                            <small class="text-muted d-block mt-1">
                                Accepted formats: 01xxxxxxx (Malaysia), 6601xxxxxxx, +601xxxxxxx
                            </small>
                        </div>

                        <div id="formatResult" style="display: none;" class="mb-3">
                            <div class="alert alert-info">
                                <strong>Formatting Result:</strong><br>
                                <small id="rawPhoneDisplay"></small><br>
                                <small id="formattedPhoneDisplay"></small>
                            </div>
                        </div>

                        <button type="button" class="btn btn-info" id="formatBtn">Format Phone Number</button>
                    </form>

                    <hr>

                    <h6 class="mt-4">Current Database Phone Numbers:</h6>
                    <table class="table table-sm table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Original</th>
                                <th>Formatted</th>
                                <th>Count</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>01155577037</td>
                                <td><code>60155577037</code></td>
                                <td><span class="badge bg-success">6</span></td>
                                <td>
                                    <a href="https://api.whatsapp.com/send?phone=60155577037&text=Test%20message" target="_blank" class="btn btn-sm btn-success">
                                        <i class="bi bi-whatsapp"></i> Test
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>01115660840</td>
                                <td><code>60115660840</code></td>
                                <td><span class="badge bg-primary">1</span></td>
                                <td>
                                    <a href="https://api.whatsapp.com/send?phone=60115660840&text=Test%20message" target="_blank" class="btn btn-sm btn-success">
                                        <i class="bi bi-whatsapp"></i> Test
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>0108032705</td>
                                <td><code>60108032705</code></td>
                                <td><span class="badge bg-primary">1</span></td>
                                <td>
                                    <a href="https://api.whatsapp.com/send?phone=60108032705&text=Test%20message" target="_blank" class="btn btn-sm btn-success">
                                        <i class="bi bi-whatsapp"></i> Test
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <hr>

                    <h6 class="mt-4">Troubleshooting:</h6>
                    <ul>
                        <li><strong>Messages not sent?</strong> The recipient must have WhatsApp installed and use the same phone number</li>
                        <li><strong>Invalid format?</strong> Check that the number starts with 0 (Malaysia) or includes country code 60</li>
                        <li><strong>Click "Test" above</strong> to open WhatsApp Web with a test message for each number</li>
                        <li><strong>Make sure you're logged into WhatsApp Web</strong> at <a href="https://web.whatsapp.com" target="_blank">web.whatsapp.com</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formatBtn').addEventListener('click', function() {
    const phone = document.getElementById('testPhone').value.trim();
    
    if (!phone) {
        alert('Please enter a phone number');
        return;
    }
    
    // Extract digits only
    const digits = phone.replace(/[^0-9]/g, '');
    
    if (digits.length === 0) {
        alert('No digits found in the phone number');
        return;
    }
    
    // Format: 0xx -> 60xx, 60xx -> 60xx, others -> as-is
    let formatted;
    if (digits.startsWith('0')) {
        formatted = '60' + digits.substring(1);
    } else if (digits.startsWith('60')) {
        formatted = digits;
    } else {
        formatted = digits;
    }
    
    // Display results
    document.getElementById('rawPhoneDisplay').innerHTML = '<strong>Raw Input:</strong> ' + phone;
    document.getElementById('formattedPhoneDisplay').innerHTML = '<strong>Formatted for WhatsApp:</strong> <code style="font-size: 1.1em; background: #e8f5e9; padding: 4px 8px;">+' + formatted + '</code>';
    document.getElementById('formatResult').style.display = 'block';
    
    // Create WhatsApp link
    const waLink = `https://api.whatsapp.com/send?phone=${formatted}&text=Test%20message`;
    console.log('WhatsApp Link:', waLink);
});

document.getElementById('testPhone').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('formatBtn').click();
    }
});
</script>
