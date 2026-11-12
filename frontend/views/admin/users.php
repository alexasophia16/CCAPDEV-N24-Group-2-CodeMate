<main class="role-page">
  <div class="role-page-header"><div><h1>User Management</h1><p>View registered users and manage account access.</p></div></div>
  <div class="role-table-wrap">
    <table class="role-table">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($users as $account): ?>
        <tr>
          <td><?= e($account['full_name']) ?></td><td><?= e($account['email']) ?></td>
          <td><?= e(role_label($account['role'])) ?></td>
          <td><span class="role-pill <?= $account['account_status'] === 'active' ? '' : 'danger' ?>"><?= e($account['account_status']) ?></span></td>
          <td><?= e(date('M j, Y', strtotime($account['created_at']))) ?></td>
          <td>
            <?php if ($account['role'] === 'admin' || (int)$account['user_id'] === (int)$currentUser['user_id']): ?>—<?php else: ?>
              <form method="post" action="<?= e(url('admin-user-status')) ?>" class="inline-action">
                
                <input type="hidden" name="user_id" value="<?= (int)$account['user_id'] ?>" />
                <input type="hidden" name="status" value="<?= $account['account_status'] === 'active' ? 'deactivated' : 'active' ?>" />
                <button class="role-btn <?= $account['account_status'] === 'active' ? 'danger' : 'primary' ?>" type="submit"><?= $account['account_status'] === 'active' ? 'Deactivate' : 'Reactivate' ?></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
