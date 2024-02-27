<?php
require __DIR__ . '/vendor/autoload.php';

// Database connection details (adjust if needed)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "barcode_database";

// Create connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to generate a unique 12-digit barcode
function generateBarcode($conn) {
    do {
        $barcode = rand(100000000000, 999999999999);
    } while ($conn->query("SELECT * FROM barcodes WHERE barcode = $barcode")->num_rows > 0);

    // Insert the barcode into the database
    $sql = "INSERT INTO barcodes (barcode) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();

    return $barcode;
}

// Update image save path if needed
$imageSavePath = 'C:/xampp/htdocs/bc/barcodes/';

// Loop to generate 9 barcodes and images
$rows = 10;
$cols = 3;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcodes</title>
    <style>
        /* Table style */
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            text-align: center;
            padding: 15px;
        }
        th {
            background-color: #f1f1f1;
            font-weight: bold;
        }
        /* Button style */
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }
        button:hover {
            background-color: #333;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        /* Centered button container */
        .button-container {
            width: 50%;
            margin: 20px auto;
            text-align: center;
        }

        /* Hide unnecessary elements for printing */
        @media print {
            body * {
                visibility: hidden;
            }
            .button-container,
            table,
            table * {
                visibility: visible;
            }
            .button-container {
                position: absolute;
                left: 0;
                top: 0;
            }
            table {
                margin: 0;
                position: absolute;
                left: 0;
                top: 0;
            }
        }
    </style>
</head>
<body>

<table>
    <thead>
        <tr>
            <th>Barcode</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($i = 0; $i < $rows; $i++) {
            echo "<tr>";
            for ($j = 0; $j < $cols; $j++) {

                // Generate a unique barcode and filename
                $barcode = generateBarcode($conn);
                $filename = $imageSavePath . $barcode . '.png';

                // Create a barcode image
                $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                $barcodeImageString = $generator->getBarcode($barcode, $generator::TYPE_CODE_128);
                $image = imagecreatefromstring($barcodeImageString);

                // Ensure image is created successfully before saving
                if ($image) {
                    imagepng($image, $filename);
                    imagedestroy($image);
                } else {
                    echo "Error: Failed to create barcode image.";
                }

                // Display the barcode image and number in the same row
                echo "<td><img src='barcodes/" . basename($filename) . "' alt='Barcode Image'><br>$barcode</td>";
            } // End inner loop
            echo "</tr>";
        } // End outer loop
        ?>
    </tbody>
</table>

<!-- Centered button container -->
<div class="button-container">
    <!-- Print Table Button -->
    <button onclick="printTable()" class="btn-primary">Print Table</button>
    <!-- Download Table Button -->
    <button onclick="downloadTable()" class="btn-primary">Download Table</button>
</div>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
    function printTable() {
        window.print();
    }

    function downloadTable() {
        html2canvas(document.querySelector("table")).then(canvas => {
            var link = document.createElement('a');
            link.href = canvas.toDataURL();
            link.download = 'table_image.png';
            link.click();
        });
    }
</script>

<?php
// Close database connection
$conn->close();
?>

</body>
</html>
