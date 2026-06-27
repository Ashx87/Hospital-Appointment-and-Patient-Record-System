<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Patient.php';
require_once 'includes/validation.php';

$pdo = Database::getInstance();
$errors = [];

//validation
if ($_SERVER['REQUEST_METHOD']==='POST') {
    validateRequired($_POST['full_name'], "Full Name", $errors);
    validateEmail($_POST['email'], $errors);
    if (strlen($_POST['password'])<8){$errors[]="Password must at least 8 characters.";}

    //one transaction
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $user=new User();
            $patient=new Patient();

            //create account in 'users' table
            $userId = $user->create([
                'role'=>'patient',
                'email'=>$_POST['email'],
                'password'=>$_POST['password'],
                'full_name'=>$_POST['full_name'],
                'phone'=>$_POST['phone']
            ]);

            //create medical profile in 'patients' table
            $patient->create($userId, [
                'date_of_birth'=>$_POST['dob'],
                'gender'=>$_POST['gender'],
                'blood_type'=>$_POST['blood_type'],
                'allergies'=>$_POST['allergies'],
                'address'=>$_POST['address']
            ]);
            $pdo->commit();
            header('Location: index.php?msg=Registration+Success');
            exit;

        }catch(Exception $e){
            $pdo->rollBack();
            header('Location: error.php?code=500&msg=' . urlencode('Registration failed. Email might be taken.'));
            exit;
        }
    }
}
?>

<form method="POST" action="register.php">
    <h1>Join HospitalCare</h1>
    <input type="text" name="full_name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="date" name="dob" required>
    <select name="gender"><option value="male">Male</option><option value="female">Female</option></select>
    <button type="submit">Register Now</button>
</form>
