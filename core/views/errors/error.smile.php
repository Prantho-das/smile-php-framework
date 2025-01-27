<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $data['code']??500 ?></title>
</head>
<body>
  <h1>
     <?php echo $data['message']??'Internal Server Error' ?>
  </h1>
</body>
</html>