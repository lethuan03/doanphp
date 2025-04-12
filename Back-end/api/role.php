<?php
require_once "../config/database.php";

$database = new Database();
$conn = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        createRole($input, $conn);
        break;
    case 'PUT':
        updateRole($input, $conn);
        break;
    case 'DELETE':
        deleteRole($input, $conn);
        break;
    case 'GET':
        if (isset($_GET['id'])) {
            getRoleById($_GET['id'], $conn);
        } else {
            getAllRoles($conn);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function createRole($input, $conn) {
    if (!isset($input['role_name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Role name is required']);
        return;
    }

    $roleName = $input['role_name'];
    $stmt = $conn->prepare("INSERT INTO Roles (role_name) VALUES (:role_name)");
    $stmt->bindParam(':role_name', $roleName);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['message' => 'Role created successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create role']);
    }
}

function updateRole($input, $conn) {
    if (!isset($input['role_id']) || !isset($input['role_name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Role ID and Role name are required']);
        return;
    }

    $roleId = $input['role_id'];
    $roleName = $input['role_name'];
    $stmt = $conn->prepare("UPDATE Roles SET role_name = :role_name WHERE role_id = :role_id");
    $stmt->bindParam(':role_name', $roleName);
    $stmt->bindParam(':role_id', $roleId);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['message' => 'Role updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update role']);
    }
}

function deleteRole($input, $conn) {
    if (!isset($input['role_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Role ID is required']);
        return;
    }

    $roleId = $input['role_id'];
    $stmt = $conn->prepare("DELETE FROM Roles WHERE role_id = :role_id");
    $stmt->bindParam(':role_id', $roleId);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['message' => 'Role deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete role']);
    }
}

function getAllRoles($conn) {
    $stmt = $conn->prepare("SELECT role_id, role_name FROM Roles");
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($roles);
}

function getRoleById($id, $conn) {
    $stmt = $conn->prepare("SELECT role_id, role_name FROM Roles WHERE role_id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($role) {
        echo json_encode($role);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Role not found']);
    }
}
?>
