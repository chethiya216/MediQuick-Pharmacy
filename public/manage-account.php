<?php
session_start();
require_once('../includes/db.php');
require_once('../includes/auth.php');

isLoggedIn();

$customerId = !empty($_SESSION['user_id'])
    ? (int) $_SESSION['user_id']
    : (int) ($_GET['customer_id'] ?? 1);

$successMessage = $_SESSION['account_success'] ?? '';
unset($_SESSION['account_success']);
$errorMessage = '';
$openModal = '';

function getCustomer(mysqli $conn, int $customerId): ?array
{
    $stmt = $conn->prepare(
        "SELECT customer_id, first_name, last_name, email, password_hash, phone, status
         FROM customers WHERE customer_id = ? LIMIT 1"
    );
    if (!$stmt) return null;
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();
    
    return $customer ?: null;
}

$customer = getCustomer($conn, $customerId);

if (!$customer) {
    die('Customer not found. Please use a valid customer_id, for example manage-account.php?customer_id=1');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Update Profile
    if ($action === 'update_profile') {
        $openModal = 'profile-modal';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $phone     = trim($_POST['phone'] ?? '');

        if ($firstName === '' || $lastName === '' || $email === '') {
            $errorMessage = 'First name, last name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please enter a valid email address.';
        } else {
            $check = $conn->prepare("SELECT customer_id FROM customers WHERE email = ? AND customer_id <> ? LIMIT 1");
            $check->bind_param('si', $email, $customerId);
            $check->execute();
            $duplicate = $check->get_result()->fetch_assoc();
            $check->close();

            if ($duplicate) {
                $errorMessage = 'This email address is already used by another customer.';
            } else {
                $stmt = $conn->prepare(
                    "UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE customer_id = ?"
                );
                $stmt->bind_param('ssssi', $firstName, $lastName, $email, $phone, $customerId);
                if ($stmt->execute()) {
                    $_SESSION['first_name'] = $firstName;
                    $_SESSION['last_name'] = $lastName;
                    $_SESSION['email'] = $email;
                    $_SESSION['account_success'] = 'Personal information updated successfully.';
                    $stmt->close();
                    header('Location: manage-account.php');
                    exit;
                }
                $errorMessage = 'Could not update your information. Database error: ' . $stmt->error;
                $stmt->close();
            }
        }
    }

    // 2. Change Password
    if ($action === 'change_password') {
        $openModal = 'password-modal';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $errorMessage = 'Please complete all password fields.';
        } elseif (!password_verify($currentPassword, $customer['password_hash'])) {
            $errorMessage = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $errorMessage = 'New password must contain at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = 'New password and confirmation do not match.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE customers SET password_hash = ? WHERE customer_id = ?");
            $stmt->bind_param('si', $newHash, $customerId);
            if ($stmt->execute()) {
                $_SESSION['account_success'] = 'Password changed successfully.';
                $stmt->close();
                header('Location: manage-account.php');
                exit;
            }
            $errorMessage = 'Could not change your password. Database error: ' . $stmt->error;
            $stmt->close();
        }
    }

    // 3. Log out of all devices
    if ($action === 'logout_all') {
        $stmt = $conn->prepare("UPDATE customers SET remember_token = NULL WHERE customer_id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            $stmt->close();
        }
        
        $_SESSION['account_success'] = 'Successfully logged out of all other devices.';
        header('Location: manage-account.php');
        exit;
    }

    // 4. Delete Account
    if ($action === 'delete_account') {
        $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            $stmt->close();
        }
        
        session_destroy();
        header('Location: login.php?deleted=1');
        exit;
    }
}

$customer = getCustomer($conn, $customerId);
require_once('../includes/header.php');
?>

<style>
/* =========================================================
   MediQuick - Account Page Styles
   ========================================================= */
.account-page{
    --mq-blue:#00A3FF;
    --mq-blue-dark:#008EDB;
    --mq-blue-light:#EAF8FF;
    --mq-green:#00C89A;
    --mq-bg:#F7F9FC;
    --mq-card:#FFFFFF;
    --mq-text:#17212B;
    --mq-muted:#718096;
    --mq-border:#E5EAF0;
    --mq-danger:#E5484D;

    background:var(--mq-bg);
    color:var(--mq-text);
    min-height:760px;
    padding:42px 0 65px;
    font-family:Arial, Helvetica, sans-serif;
}
.account-page *{box-sizing:border-box;}
.account-container{
    width:calc(100% - 40px);
    max-width:1180px;
    margin:0 auto;
    display:grid;
    grid-template-columns:285px minmax(0,1fr);
    gap:34px;
    align-items:start;
}
.account-sidebar{
    background:var(--mq-card);
    border:1px solid var(--mq-border);
    border-radius:16px;
    padding:26px 15px 24px;
    box-shadow:0 5px 22px rgba(22,38,58,.05);
    position:sticky;
    top:25px;
}
.account-sidebar-title{
    display:flex;
    align-items:center;
    gap:10px;
    padding:5px 14px 22px;
    margin-bottom:5px;
    border-bottom:1px solid var(--mq-border);
}
.account-sidebar-title .title-icon{
    width:35px;height:35px;display:flex;align-items:center;justify-content:center;
    border-radius:10px;background:var(--mq-blue-light);color:var(--mq-blue);font-size:16px;
}
.account-sidebar-title strong{font-size:14px;color:var(--mq-text);font-weight:700;}
.account-sidebar-title span{display:block;margin-top:2px;color:var(--mq-muted);font-size:11px;}
.account-nav{display:flex;flex-direction:column;gap:7px;padding-top:14px;}
.account-nav a{
    position:relative;width:100%;display:flex;align-items:center;gap:13px;min-height:58px;
    padding:12px 14px;border-radius:10px;text-decoration:none !important;color:#5E6B78;
    font-size:15px;font-weight:500;transition:all .2s ease;
}
.account-nav a .nav-icon{
    width:34px;height:34px;display:flex;align-items:center;justify-content:center;
    border-radius:9px;background:#F4F6F8;color:#7A8794;font-size:14px;
}
.account-nav a:hover{color:var(--mq-blue);background:#F5FBFF;}
.account-nav a.active{color:var(--mq-blue);background:var(--mq-blue-light);font-weight:700;}
.account-main{min-width:0;}
.account-heading{margin-bottom:25px;}
.account-heading .eyebrow{display:inline-flex;align-items:center;gap:7px;color:var(--mq-blue);font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:7px;}
.account-heading h1{margin:0 0 7px;font-size:28px;font-weight:700;color:var(--mq-text);}
.account-heading p{margin:0;color:var(--mq-muted);font-size:14px;}
.account-card{background:var(--mq-card);border:1px solid var(--mq-border);border-radius:15px;margin-bottom:18px;overflow:hidden;box-shadow:0 5px 22px rgba(22,38,58,.045);}
.account-card-head{display:flex;align-items:center;justify-content:space-between;padding:19px 22px;border-bottom:1px solid var(--mq-border);}
.card-title-wrap{display:flex;align-items:center;gap:12px;}
.card-icon{width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:var(--mq-blue-light);color:var(--mq-blue);}
.card-title-wrap h3{margin:0;font-size:16px;font-weight:700;}
.card-title-wrap p{margin:3px 0 0;font-size:11px;color:var(--mq-muted);}
.account-card-body{padding:23px 22px;}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.info-box{padding:15px 16px;background:#FAFBFC;border:1px solid #EDF0F3;border-radius:10px;}
.info-box label{display:block;color:#8A96A3;font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:7px;}
.info-box .info-value{color:var(--mq-text);font-size:14px;font-weight:600;word-break:break-word;}
.account-btn{border:0;border-radius:8px;padding:9px 15px;background:var(--mq-blue);color:#fff;font-size:12px;font-weight:700;cursor:pointer;transition:.2s ease;}
.account-btn:hover{background:var(--mq-blue-dark);}
.security-row, .action-item{display:flex;align-items:center;justify-content:space-between;padding:14px 0;}
.security-row + .security-row, .action-item + .action-item{border-top:1px solid var(--mq-border);}
.security-left, .action-left{display:flex;align-items:center;gap:13px;}
.security-mini-icon{width:37px;height:37px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:#F4F7FA;color:#687887;}
.security-left .label, .action-left .label{font-size:14px;font-weight:600;color:var(--mq-text);}
.security-left .desc, .action-left .desc{margin-top:3px;font-size:11px;color:var(--mq-muted);}
.password-dots{color:#596674;font-size:14px;letter-spacing:3px;}
.action-link{border:0;background:transparent;color:var(--mq-blue);font-size:12px;font-weight:700;cursor:pointer;padding:8px 4px;}
.action-link:hover{text-decoration:underline;}
.action-link.delete{color:var(--mq-danger);}
.alert-success{background:#E6F4EA;color:#137333;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:13px;font-weight:600;}
</style>

<div class="account-page">
    <div class="account-container">
        <!-- Sidebar -->
        <aside class="account-sidebar">
            <div class="account-sidebar-title">
                <div class="title-icon"><i class="fa fa-user"></i></div>
                <div>
                    <strong>Account Menu</strong>
                    <span>Manage your account</span>
                </div>
            </div>
            <nav class="account-nav">
                <a href="#profile"><span class="nav-icon"><i class="fa fa-user"></i></span><span>Profile</span></a>
                <a href="#security"><span class="nav-icon"><i class="fa fa-lock"></i></span><span>Security</span></a>
                <a href="manage-account.php" class="active"><span class="nav-icon"><i class="fa fa-cog"></i></span><span>Account</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="account-main">
            <div class="account-heading">
                <div class="eyebrow">MediQuick Account</div>
                <h1>Account overview</h1>
                <p>Manage your personal details, security and account settings.</p>
            </div>

            <?php if (!empty($successMessage)): ?>
                <div class="alert-success"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>

            <!-- Personal info card -->
            <section class="account-card" id="profile">
                <div class="account-card-head">
                    <div class="card-title-wrap">
                        <div class="card-icon"><i class="fa fa-user"></i></div>
                        <div>
                            <h3>Personal information</h3>
                            <p>Your basic account details</p>
                        </div>
                    </div>
                    <button type="button" class="account-btn" id="edit-profile-btn">Edit</button>
                </div>
                <div class="account-card-body">
                    <div class="info-grid">
                        <div class="info-box"><label>First name</label><div class="info-value"><?= htmlspecialchars($customer['first_name']) ?></div></div>
                        <div class="info-box"><label>Last name</label><div class="info-value"><?= htmlspecialchars($customer['last_name']) ?></div></div>
                        <div class="info-box"><label>Email address</label><div class="info-value"><?= htmlspecialchars($customer['email']) ?></div></div>
                        <div class="info-box"><label>Phone number</label><div class="info-value"><?= htmlspecialchars($customer['phone'] ?? '') ?></div></div>
                    </div>
                </div>
            </section>

            <!-- Security card -->
            <section class="account-card" id="security">
                <div class="account-card-head">
                    <div class="card-title-wrap">
                        <div class="card-icon"><i class="fa fa-shield"></i></div>
                        <div>
                            <h3>Password &amp; security</h3>
                            <p>Keep your account protected</p>
                        </div>
                    </div>
                    <button type="button" class="account-btn" id="change-password-btn">Change</button>
                </div>
                <div class="account-card-body">
                    <div class="security-row" style="border-top:0; padding-top:0;">
                        <div class="security-left">
                            <div class="security-mini-icon"><i class="fa fa-key"></i></div>
                            <div>
                                <div class="label">Password</div>
                                <div class="desc">Encrypted securely</div>
                            </div>
                        </div>
                        <div class="password-dots">••••••••••••••••</div>
                    </div>
                </div>
            </section>

            <!-- Account actions card -->
            <section class="account-card">
                <div class="account-card-head">
                    <div class="card-title-wrap">
                        <div class="card-icon"><i class="fa fa-cog"></i></div>
                        <div>
                            <h3>Account actions</h3>
                            <p>Manage your active sessions and account</p>
                        </div>
                    </div>
                </div>
                <div class="account-card-body">
                    <!-- Log out all devices form -->
                    <form method="POST" action="manage-account.php" onsubmit="return confirm('Are you sure you want to log out of all other devices?');" class="action-item" style="border-top:0; padding-top:0;">
                        <input type="hidden" name="action" value="logout_all">
                        <div class="action-left">
                            <div class="label">Log out of all devices</div>
                            <div class="desc">Ends every active session except this one.</div>
                        </div>
                        <button type="submit" class="action-link">Log out all devices</button>
                    </form>

                    <!-- Delete account form -->
                    <form method="POST" action="manage-account.php" id="delete-account-form" class="action-item">
                        <input type="hidden" name="action" value="delete_account">
                        <div class="action-left">
                            <div class="label">Delete account</div>
                            <div class="desc">Permanently removes your account and order history. This cannot be undone.</div>
                        </div>
                        <button type="button" class="action-link delete" id="delete-account-btn">Delete account</button>
                    </form>
                </div>
            </section>
        </main>
    </div>
</div>

<!-- Edit Personal Information Modal -->
<div class="account-modal" id="profile-modal" aria-hidden="true">
 <div class="account-modal-box" role="dialog" aria-modal="true">
  <div class="account-modal-head"><div class="account-modal-title"><div class="modal-icon"><i class="fa fa-user"></i></div><div><h3>Edit personal information</h3><p>Update your account details</p></div></div><button type="button" class="modal-close" data-close-modal="profile-modal"><i class="fa fa-times"></i></button></div>
  <form method="POST" action="manage-account.php">
   <input type="hidden" name="action" value="update_profile">
   <div class="account-modal-body">
    <?php if ($openModal === 'profile-modal' && $errorMessage !== ''): ?><div class="modal-message error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
    <div class="form-grid">
     <div class="form-group"><label>First name</label><input class="form-control" name="first_name" maxlength="50" value="<?= htmlspecialchars($_POST['first_name'] ?? $customer['first_name']) ?>" required></div>
     <div class="form-group"><label>Last name</label><input class="form-control" name="last_name" maxlength="50" value="<?= htmlspecialchars($_POST['last_name'] ?? $customer['last_name']) ?>" required></div>
     <div class="form-group full"><label>Email address</label><input class="form-control" name="email" type="email" maxlength="100" value="<?= htmlspecialchars($_POST['email'] ?? $customer['email']) ?>" required></div>
     <div class="form-group full"><label>Phone number</label><input class="form-control" name="phone" type="tel" maxlength="20" value="<?= htmlspecialchars($_POST['phone'] ?? ($customer['phone'] ?? '')) ?>"></div>
    </div>
   </div>
   <div class="account-modal-footer"><button type="button" class="modal-cancel" data-close-modal="profile-modal">Cancel</button><button type="submit" class="account-btn">Save changes</button></div>
  </form>
 </div>
</div>

<!-- Change Password Modal -->
<div class="account-modal" id="password-modal" aria-hidden="true">
 <div class="account-modal-box" role="dialog" aria-modal="true">
  <div class="account-modal-head"><div class="account-modal-title"><div class="modal-icon"><i class="fa fa-lock"></i></div><div><h3>Change password</h3><p>Update your account password</p></div></div><button type="button" class="modal-close" data-close-modal="password-modal"><i class="fa fa-times"></i></button></div>
  <form method="POST" action="manage-account.php" id="password-form">
   <input type="hidden" name="action" value="change_password">
   <div class="account-modal-body">
    <?php if ($openModal === 'password-modal' && $errorMessage !== ''): ?><div class="modal-message error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
    <p class="password-note">Enter your current password and choose a new password.</p>
    <div class="form-group"><label>Current password</label><input class="form-control" name="current_password" type="password" required></div>
    <div class="form-group"><label>New password</label><input class="form-control" id="new-password" name="new_password" type="password" minlength="6" required></div>
    <div class="form-group"><label>Confirm new password</label><input class="form-control" id="confirm-password" name="confirm_password" type="password" minlength="6" required></div>
   </div>
   <div class="account-modal-footer"><button type="button" class="modal-cancel" data-close-modal="password-modal">Cancel</button><button type="submit" class="account-btn">Update password</button></div>
  </form>
 </div>
</div>

<style>
.account-modal{display:none;position:fixed;inset:0;z-index:99999;background:rgba(15,28,42,.55);padding:20px;align-items:center;justify-content:center}
.account-modal.show{display:flex}
.account-modal-box{width:100%;max-width:500px;background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.22);overflow:hidden}
.account-modal-head{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--mq-border)}
.account-modal-title{display:flex;align-items:center;gap:12px}
.modal-icon{width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:var(--mq-blue-light);color:var(--mq-blue)}
.account-modal-title h3{margin:0;font-size:16px}
.account-modal-title p{margin:3px 0 0;font-size:11px;color:var(--mq-muted)}
.modal-close{border:0;background:#F4F6F8;color:#718096;width:34px;height:34px;border-radius:9px;cursor:pointer}
.account-modal-body{padding:22px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}
.form-group{margin-bottom:15px}
.form-group.full{grid-column:1/-1}
.form-group label{display:block;margin-bottom:7px;font-size:11px;font-weight:700;color:#687887}
.form-control{width:100%;height:43px;border:1px solid #DDE4EA;border-radius:9px;padding:0 12px;font-size:13px;outline:none}
.form-control:focus{border-color:var(--mq-blue);box-shadow:0 0 0 3px rgba(0,163,255,.1)}
.password-note{font-size:11px;color:var(--mq-muted);margin:0 0 15px}
.modal-message{padding:10px 12px;border-radius:8px;font-size:12px;font-weight:600;margin-bottom:15px}
.modal-message.error{background:#FFF1F1;color:#C7363B;border:1px solid #FFD5D7}
.account-modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:15px 22px;border-top:1px solid var(--mq-border);background:#FAFBFC}
.modal-cancel{border:1px solid #DDE4EA;background:#fff;color:#667585;border-radius:8px;padding:9px 15px;font-size:12px;font-weight:700;cursor:pointer}
</style>

<script>
(function(){
  // Delete account confirmation trigger
  document.getElementById('delete-account-btn')?.addEventListener('click', function(){
    if(confirm('WARNING: Are you absolutely sure you want to delete your account? This action is permanent and cannot be undone.')) {
      document.getElementById('delete-account-form').submit();
    }
  });

  // Modal open / close controllers
  function openModal(id){const m = document.getElementById(id);if(!m)return;m.classList.add('show');m.setAttribute('aria-hidden','false');document.body.style.overflow='hidden';}
  function closeModal(id){const m = document.getElementById(id);if(!m)return;m.classList.remove('show');m.setAttribute('aria-hidden','true');if(!document.querySelector('.account-modal.show'))document.body.style.overflow='';}
  
  document.getElementById('edit-profile-btn')?.addEventListener('click', () => openModal('profile-modal'));
  document.getElementById('change-password-btn')?.addEventListener('click', () => openModal('password-modal'));
  document.querySelectorAll('[data-close-modal]').forEach(b => b.addEventListener('click', () => closeModal(b.getAttribute('data-close-modal'))));
  document.querySelectorAll('.account-modal').forEach(m => m.addEventListener('click', e => {if(e.target === m) closeModal(m.id);}));
  document.addEventListener('keydown', e => {if(e.key === 'Escape') document.querySelectorAll('.account-modal.show').forEach(m => closeModal(m.id));});
  
  // Password matching validation
  document.getElementById('password-form')?.addEventListener('submit', function(e){
    const n = document.getElementById('new-password'), c = document.getElementById('confirm-password');
    if(n.value !== c.value){
      e.preventDefault();
      alert('New password and confirmation do not match.');
      c.focus();
    }
  });

  <?php if($openModal !== ''): ?>
    openModal(<?= json_encode($openModal) ?>);
  <?php endif; ?>
})();
</script>

<?php require_once('../includes/footer.php'); ?>