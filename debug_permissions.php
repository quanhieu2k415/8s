<?php
/**
 * Debug permission check
 */

require_once __DIR__ . '/admin/includes/auth_check.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Debug Permission Check</h2>";
echo "<p>Current User: <strong>" . htmlspecialchars($currentUser['username']) . "</strong></p>";
echo "<p>Role: <strong>" . htmlspecialchars($userRole) . "</strong></p>";

echo "<h3>1. Permission Variables (from auth_check.php):</h3>";
echo "<ul>";
echo "<li>\$canManageCMS: " . ($canManageCMS ? 'TRUE ✅' : 'FALSE ❌') . "</li>";
echo "<li>\$canManageUsers: " . ($canManageUsers ? 'TRUE ✅' : 'FALSE ❌') . "</li>";
echo "<li>\$canAccessSettings: " . ($canAccessSettings ? 'TRUE ✅' : 'FALSE ❌') . "</li>";
echo "<li>\$canViewAllLogs: " . ($canViewAllLogs ? 'TRUE ✅' : 'FALSE ❌') . "</li>";
echo "<li>\$canViewAllReports: " . ($canViewAllReports ? 'TRUE ✅' : 'FALSE ❌') . "</li>";
echo "<li>\$canExportData: " . ($canExportData ? 'TRUE ✅' : 'FALSE ❌') . "</li>";
echo "</ul>";

echo "<h3>2. Direct Permission Check (Permission::check):</h3>";
$permissionsToCheck = [
    'cms.manage' => 'Quản lý CMS',
    'content.manage_all' => 'Quản lý tất cả nội dung',
    'news.create' => 'Tạo tin tức',
    'settings.view' => 'Xem cấu hình',
    'logs.view_all' => 'Xem tất cả logs',
    'users.view_all' => 'Xem tất cả users',
    'registrations.view_all' => 'Xem tất cả đăng ký'
];

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Permission Key</th><th>Tên</th><th>Có quyền?</th></tr>";
foreach ($permissionsToCheck as $key => $name) {
    $hasPermission = $permission->check($userRole, $key);
    echo "<tr>";
    echo "<td><code>$key</code></td>";
    echo "<td>$name</td>";
    echo "<td>" . ($hasPermission ? '✅ CÓ' : '❌ KHÔNG') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>3. Role Permissions in Database:</h3>";
$rolePerms = $permission->getRolePermissions($userRole);
if (empty($rolePerms)) {
    echo "<p style='color:red'>Không có quyền nào được gán cho role '$userRole'</p>";
} else {
    echo "<p>Role '$userRole' có " . count($rolePerms) . " quyền:</p>";
    echo "<ul>";
    foreach ($rolePerms as $perm) {
        echo "<li><code>$perm</code></li>";
    }
    echo "</ul>";
}

echo "<h3>4. Gợi ý:</h3>";
echo "<p>Nếu admin đã cấp quyền nhưng đây vẫn hiện KHÔNG, hãy <strong>đăng xuất và đăng nhập lại</strong>.</p>";
