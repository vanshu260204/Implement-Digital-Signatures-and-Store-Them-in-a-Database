<!-- How to Implement Digital Signatures and Store Them in a Database Using HTML, JS and PHP -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form with Signature Pad</title>
    <style>
        .error {
            color: #ff0000;
        }
        #signature-pad {
            border: 1px solid #000;
            width: 100%;
            height: 200px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
</head>
<body>

<?php
// Define variables and set to empty values
$fullname = $email = $gender = $comment = $number = $age = $signature = "";
$fullnameErr = $emailErr = $ageErr = "";

// Function to sanitize input data
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    if (empty($_POST["name"])) {
        $fullnameErr = "Full Name is required";
    } else {
        $fullname = test_input($_POST["name"]);
    }

    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    if (empty($_POST["age"])) {
        $ageErr = "Age is required";
    } else {
        $age = test_input($_POST["age"]);
        if (!is_numeric($age)) {
            $ageErr = "Age must be a number";
        }
    }

    // If no errors, process the form
    if (empty($fullnameErr) && empty($emailErr) && empty($ageErr)) {
        // Get form data
        $gender = test_input($_POST["gender"]);
        $comment = test_input($_POST["comment"]);
        $number = test_input($_POST["number"]);
        $signature = $_POST["signature"]; // No need to sanitize base64 data

        // Insert data into the database using prepared statements
        $conn = new mysqli("localhost", "root", "", "validation");

        // Check the database connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare the SQL statement to prevent SQL injection
        $sql = $conn->prepare("INSERT INTO users (fullname, email, gender, age, number, comment, signature) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");

        // Check if the query was prepared successfully
        if ($sql === false) {
            die("Error preparing statement: " . $conn->error);
        }

        // Bind the parameters
        $sql->bind_param("sssiiss", $fullname, $email, $gender, $age, $number, $comment, $signature);

        // Execute the statement
        if ($sql->execute()) {
            echo "New record created successfully!";
        } else {
            echo "Error executing query: " . $sql->error;
        }

        // Close the statement and connection
        $sql->close();
        $conn->close();
    }
}
?>

<h2>Form with Signature Pad</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

    Full Name: <input type="text" name="name" value="<?php echo $fullname; ?>">
    <span class="error"><?php echo $fullnameErr; ?></span>
    <br><br>
    E-mail: <input type="text" name="email" value="<?php echo $email; ?>">
    <span class="error"><?php echo $emailErr; ?></span>
    <br><br>
    Age: <input type="text" name="age" value="<?php echo $age; ?>">
    <span class="error"><?php echo $ageErr; ?></span>
    <br><br>
    Number (optional): <input type="text" name="number" value="<?php echo $number; ?>">
    <br><br>
    Comment: <textarea name="comment" rows="10" cols="30"><?php echo $comment; ?></textarea>
    <br><br>
    Gender:
    <input type="radio" name="gender" value="female" <?php if ($gender == "female") echo "checked"; ?>> Female
    <input type="radio" name="gender" value="male" <?php if ($gender == "male") echo "checked"; ?>> Male
    <br><br>

    <!-- Signature Pad -->
    <label for="signature-pad">Signature:</label><br>
    <canvas id="signature-pad"></canvas>
    <br>
    <button type="button" id="clear-signature">Clear</button>
    <br><br>

    <!-- Hidden input to send the signature as base64 -->
    <input type="hidden" name="signature" id="signature">

    <input type="submit" value="Submit">

</form>

<script>
    // Initialize Signature Pad
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas);

    // Clear the signature pad
    document.getElementById('clear-signature').addEventListener('click', function () {
        signaturePad.clear();
    });

    // Before submitting the form, set the signature in the hidden input
    const form = document.querySelector('form');
    form.addEventListener('submit', function (e) {
        // Get the signature as base64 image
        const signatureData = signaturePad.toDataURL();
        document.getElementById('signature').value = signatureData;
    });
</script>

</body>
</html>
