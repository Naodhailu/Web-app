<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// ---- Pre-populated user accounts with balances ----
$accounts_file = 'accounts.json';
if (!file_exists($accounts_file)) {
    $default_accounts = [
        ["name" => "Naod Hailu",      "account" => "WCU-1001", "balance" => 12000],
        ["name" => "Abel Tadesse",    "account" => "WCU-1002", "balance" => 8500],
        ["name" => "Sara Kebede",     "account" => "WCU-1003", "balance" => 3200],
        ["name" => "Dawit Mekonnen",  "account" => "WCU-1004", "balance" => 15000],
        ["name" => "Hana Girma",      "account" => "WCU-1005", "balance" => 4800],
        ["name" => "Yonas Bekele",    "account" => "WCU-1006", "balance" => 6200],
        ["name" => "Meron Alemu",     "account" => "WCU-1007", "balance" => 2100],
        ["name" => "Kaleb Desta",     "account" => "WCU-1008", "balance" => 9300],
        ["name" => "Tigist Worku",    "account" => "WCU-1009", "balance" => 5000],
        ["name" => "Samuel Tesfaye",  "account" => "WCU-1010", "balance" => 5500],
    ];
    file_put_contents($accounts_file, json_encode($default_accounts, JSON_PRETTY_PRINT));
}

$accounts = json_decode(file_get_contents($accounts_file), true);

// ---- Loan history ----
$loans_file = 'loans.json';
if (!file_exists($loans_file)) {
    file_put_contents($loans_file, json_encode([]));
}
$loans = json_decode(file_get_contents($loans_file), true);

// ---- Handle loan request ----
$loan_error = '';
$loan_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_loan') {
    $req_name    = trim($_POST['req_name'] ?? '');
    $req_account = trim($_POST['req_account'] ?? '');
    $req_amount  = floatval($_POST['req_amount'] ?? 0);

    // Find the account
    $found = false;
    $found_index = -1;
    foreach ($accounts as $i => $acc) {
        if (strcasecmp($acc['name'], $req_name) === 0 && $acc['account'] === $req_account) {
            $found = true;
            $found_index = $i;
            break;
        }
    }

    if (!$found) {
        $loan_error = 'Account not found. Please check the name and account number.';
    } elseif ($req_amount <= 0) {
        $loan_error = 'Please enter a valid loan amount.';
    } elseif ($accounts[$found_index]['balance'] < 5000) {
        $loan_error = 'Loan denied. Account balance must be at least 5,000 Birr to qualify. Current balance: ' . number_format($accounts[$found_index]['balance']) . ' Birr.';
    } elseif ($req_amount > 40000) {
        $loan_error = 'Loan denied. Maximum loan amount is 40,000 Birr.';
    } else {
        // Approve the loan
        $loan_record = [
            "name"    => $accounts[$found_index]['name'],
            "account" => $accounts[$found_index]['account'],
            "amount"  => $req_amount,
            "date"    => date('Y-m-d H:i:s'),
            "status"  => "Approved"
        ];
        $loans[] = $loan_record;
        file_put_contents($loans_file, json_encode($loans, JSON_PRETTY_PRINT));

        // Update balance (add the loan amount)
        $accounts[$found_index]['balance'] += $req_amount;
        file_put_contents($accounts_file, json_encode($accounts, JSON_PRETTY_PRINT));

        $loan_success = 'Loan of ' . number_format($req_amount) . ' Birr approved for ' . htmlspecialchars($accounts[$found_index]['name']) . '! New balance: ' . number_format($accounts[$found_index]['balance']) . ' Birr.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Wachemo SE Section A</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="dashboard-body">
    <div class="background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <!-- Top Navigation Bar -->
    <nav class="topbar">
        <div class="topbar-brand">Wachemo SE Section A</div>
        <div class="topbar-right">
            <span class="topbar-user">👋 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="logout" value="1">
                <button type="submit" class="btn btn-logout">Logout</button>
            </form>
        </div>
    </nav>

    <main class="dashboard">
        <!-- Loan Request Card -->
        <section class="card loan-card">
            <h2>Request a Loan</h2>
            <p class="card-subtitle">Enter the account holder's name, account number, and loan amount.</p>
            <p class="card-note">Minimum balance: <strong>5,000 Birr</strong> &nbsp;|&nbsp; Max loan: <strong>40,000 Birr</strong></p>

            <?php if ($loan_error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($loan_error); ?></div>
            <?php endif; ?>
            <?php if ($loan_success): ?>
                <div class="alert alert-success"><?php echo $loan_success; ?></div>
            <?php endif; ?>

            <form method="POST" class="loan-form">
                <input type="hidden" name="action" value="request_loan">
                <div class="form-row">
                    <div class="input-group-dash">
                        <label for="req_name">Full Name</label>
                        <input type="text" id="req_name" name="req_name" placeholder="e.g. Naod Hailu" required>
                    </div>
                    <div class="input-group-dash">
                        <label for="req_account">Account Number</label>
                        <input type="text" id="req_account" name="req_account" placeholder="e.g. WCU-1001" required>
                    </div>
                    <div class="input-group-dash">
                        <label for="req_amount">Loan Amount (Birr)</label>
                        <input type="number" id="req_amount" name="req_amount" placeholder="e.g. 20000" min="1" max="40000" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-loan">Submit Loan Request</button>
            </form>
        </section>

        <!-- User Accounts Table -->
        <section class="card table-card">
            <h2>Registered Accounts</h2>
            <p class="card-subtitle">All users and their current balances. A green badge means they are eligible to borrow.</p>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Account</th>
                            <th>Balance (Birr)</th>
                            <th>Eligibility</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $i => $acc): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($acc['name']); ?></td>
                            <td><code><?php echo htmlspecialchars($acc['account']); ?></code></td>
                            <td><?php echo number_format($acc['balance']); ?></td>
                            <td>
                                <?php if ($acc['balance'] >= 5000): ?>
                                    <span class="badge badge-eligible">Eligible</span>
                                <?php else: ?>
                                    <span class="badge badge-ineligible">Not Eligible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Loan History -->
        <?php if (!empty($loans)): ?>
        <section class="card table-card">
            <h2>Loan History</h2>
            <p class="card-subtitle">All approved loan transactions.</p>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Account</th>
                            <th>Loan Amount (Birr)</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $i => $loan): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($loan['name']); ?></td>
                            <td><code><?php echo htmlspecialchars($loan['account']); ?></code></td>
                            <td><?php echo number_format($loan['amount']); ?></td>
                            <td><?php echo htmlspecialchars($loan['date']); ?></td>
                            <td><span class="badge badge-eligible"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>
    </main>
</body>
</html>
