<?php
// search.php - API endpoint for searching books

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include the database connection
require_once 'db_config.php';

// GET - Search books with multiple filters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Initialize the WHERE clause
    $where_clauses = [];
    $params = [];
    
    // Add title filter if provided
    if (isset($_GET['title']) && !empty($_GET['title'])) {
        $title = mysqli_real_escape_string($conn, $_GET['title']);
        $where_clauses[] = "title LIKE '%$title%'";
    }
    
    // Add author filter if provided
    if (isset($_GET['author']) && !empty($_GET['author'])) {
        $author = mysqli_real_escape_string($conn, $_GET['author']);
        $where_clauses[] = "author LIKE '%$author%'";
    }
    
    // Add publication year filter if provided
    if (isset($_GET['year']) && !empty($_GET['year'])) {
        $year = mysqli_real_escape_string($conn, $_GET['year']);
        $where_clauses[] = "publication_year = '$year'";
    }
    
    // Construct the SQL query
    $sql = "SELECT id, title, author, isbn, publication_year FROM books";
    
    // Add WHERE clause if filters are applied
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    // Add ordering
    $sql .= " ORDER BY title ASC";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
        exit();
    }
    
    $books = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $books[] = $row;
    }
    
    echo json_encode($books);
}

// Close connection
mysqli_close($conn);