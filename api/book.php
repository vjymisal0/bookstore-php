<?php
// book.php - API endpoint for books CRUD operations

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include the database connection
require_once 'db_config.php';

// GET - Fetch all books
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        // Fetch a specific book by ID
        $id = mysqli_real_escape_string($conn, $_GET['id']);
        $sql = "SELECT id, title, author, isbn, publication_year FROM books WHERE id = $id";
    } else {
        // Fetch all books
        $sql = "SELECT id, title, author, isbn, publication_year FROM books ORDER BY id DESC";
    }
    
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

// POST - Add a new book
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['title']) || !isset($data['author']) || !isset($data['isbn'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }
    
    // Sanitize inputs
    $title = mysqli_real_escape_string($conn, $data['title']);
    $author = mysqli_real_escape_string($conn, $data['author']);
    $isbn = mysqli_real_escape_string($conn, $data['isbn']);
    $publication_year = isset($data['publication_year']) ? 
                        mysqli_real_escape_string($conn, $data['publication_year']) : NULL;
    
    // Execute query
    $sql = "INSERT INTO books (title, author, isbn, publication_year) 
            VALUES ('$title', '$author', '$isbn', " . 
            ($publication_year ? "'$publication_year'" : "NULL") . ")";
    
    if (mysqli_query($conn, $sql)) {
        $newId = mysqli_insert_id($conn);
        http_response_code(201);
        echo json_encode([
            'id' => $newId, 
            'message' => 'Book added successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
    }
}

// PUT - Update an existing book
else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Book ID is required']);
        exit();
    }
    
    // Sanitize inputs
    $id = mysqli_real_escape_string($conn, $data['id']);
    $title = isset($data['title']) ? 
             mysqli_real_escape_string($conn, $data['title']) : null;
    $author = isset($data['author']) ? 
              mysqli_real_escape_string($conn, $data['author']) : null;
    $isbn = isset($data['isbn']) ? 
            mysqli_real_escape_string($conn, $data['isbn']) : null;
    $publication_year = isset($data['publication_year']) ? 
                        mysqli_real_escape_string($conn, $data['publication_year']) : null;
    
    // Build the update query dynamically based on provided fields
    $updates = [];
    if ($title !== null) $updates[] = "title = '$title'";
    if ($author !== null) $updates[] = "author = '$author'";
    if ($isbn !== null) $updates[] = "isbn = '$isbn'";
    if ($publication_year !== null) $updates[] = "publication_year = '$publication_year'";
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['error' => 'No fields to update']);
        exit();
    }
    
    $sql = "UPDATE books SET " . implode(", ", $updates) . " WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['message' => 'Book updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
    }
}

// DELETE - Delete a book
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Book ID is required']);
        exit();
    }
    
    // Sanitize input
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Execute query
    $sql = "DELETE FROM books WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['message' => 'Book deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
    }
}

// Close connection
mysqli_close($conn);