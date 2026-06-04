<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Category.php";

function addCategory() {
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);
    $name = $data['name'] ?? '';

    if ($name == '') {
        echo json_encode(["status" => false, "message" => "Category name is required"]);
        return;
    }

    $categoryModel = new Category($conn);
    $categoryModel->create($name);

    echo json_encode(["status" => true, "message" => "Category added successfully"]);
}

function getCategories() {
    global $conn;

    $categoryModel = new Category($conn);

    echo json_encode([
        "status" => true,
        "data" => $categoryModel->getAll()
    ]);
}

function updateCategory() {
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data['id'] ?? '';
    $name = $data['name'] ?? '';

    if ($id == '' || $name == '') {
        echo json_encode(["status" => false, "message" => "Id and name are required"]);
        return;
    }

    $categoryModel = new Category($conn);
    $categoryModel->update($id, $name);

    echo json_encode(["status" => true, "message" => "Category updated successfully"]);
}

function deleteCategory() {
    global $conn;

    $id = $_GET['id'] ?? '';

    if ($id == '') {
        echo json_encode(["status" => false, "message" => "Category id is required"]);
        return;
    }

    $categoryModel = new Category($conn);
    $categoryModel->delete($id);

    echo json_encode(["status" => true, "message" => "Category deleted successfully"]);
}