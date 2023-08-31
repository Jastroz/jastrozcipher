<?php
function gcd($a, $b) {
    while ($b != 0) {
        $temp = $b;
        $b = $a % $b;
        $a = $temp;
    }
    return $a;
}

function modInverse($a, $m) {
    $m0 = $m;
    $x0 = 0;
    $x1 = 1;

    if ($m == 1) {
        return 0;
    }

    while ($a > 1) {
        $q = intval($a / $m);
        $t = $m;
        $m = $a % $m;
        $a = $t;
        $t = $x0;
        $x0 = $x1 - $q * $x0;
        $x1 = $t;
    }

    if ($x1 < 0) {
        $x1 += $m0;
    }

    return $x1;
}

function affineEncrypt($text, $a, $b) {
    $result = "";
    $text = preg_replace("/[^A-Za-zÑñ]/u", "", $text); // Filtra solo caracteres del alfabeto español
    $text = strtoupper($text); // Convertir todo a mayúsculas

    $alphabet_values = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
        'K', 'L', 'M', 'N', 'Ñ', 'O', 'P', 'Q', 'R', 'S',
        'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
    );

    $m = 27; // Tamaño del alfabeto, incluyendo 'ñ'
    $a_inverse = modInverse($a, $m);

    for ($i = 0; $i < mb_strlen($text); $i++) {
        $char = mb_substr($text, $i, 1);
        
        if (in_array($char, $alphabet_values)) {
            $char_value = array_search($char, $alphabet_values);
            $encrypted_value = ($a * $char_value + $b) % $m;
            $encrypted_char = $alphabet_values[$encrypted_value];
        } elseif ($char == 'Ñ') {
            $encrypted_value = ($a * 14 + $b) % $m; // Ñ en posición 14
            $encrypted_char = $alphabet_values[$encrypted_value];
        } elseif ($char == 'ñ') {
            $encrypted_value = ($a * 14 + $b) % $m; // ñ en posición 14
            $encrypted_char = strtolower($alphabet_values[$encrypted_value]);
        } else {
            $encrypted_char = $char;
        }
        
        $result .= $encrypted_char;
    }

    return $result;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $texto = $_POST["text"];
    $a = intval($_POST["a"]);
    $b = intval($_POST["b"]);

    if (gcd($a, 27) != 1 || gcd($a, $b) != 1) {
        echo "<h1>Error: 'a' y 'b' deben ser coprimos.</h1>";

    } else {
        $cifrado = affineEncrypt($texto, $a, $b);

        // Calcular la frecuencia de cada letra en el texto cifrado
        $frecuencias = array_count_values(str_split($cifrado));
        arsort($frecuencias);

        // Tomar las 4 letras más frecuentes
        $letrasFrecuentes = array_slice($frecuencias, 0, 4, true);
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Resultado del cifrado</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #0c6bca;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
      background-color: #ffffff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      border-radius: 5px;
      margin-top: 30px;
    }
    h1 {
      color: #333333;
    }
    p {
      color: #666666;
    }
    pre {
      background-color: #f5f5f5;
      padding: 10px;
      border-radius: 5px;
      overflow-x: auto;
    }
    a {
      color: #007bff;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
    <div class="container">
        <h1>Resultado del Cifrado Afín</h1>
        <?php if (isset($cifrado)) : ?>
            <p>Texto original:</p>
            <pre><?php echo $texto; ?></pre>
            <p>Texto cifrado:</p>
            <pre><?php echo $cifrado; ?></pre>
            
            <!-- Gráfico de columnas -->
            <div id="chart_div"></div>
            
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <script type="text/javascript">
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Letra');
                    data.addColumn('number', 'Frecuencia');

                    // Agregar datos de las letras frecuentes al gráfico
                    <?php foreach ($letrasFrecuentes as $letra => $frecuencia) : ?>
                        data.addRows([['<?php echo $letra; ?>', <?php echo $frecuencia; ?>]]);
                    <?php endforeach; ?>

                    var options = {
                        title: 'Letras más frecuentes en el texto cifrado',
                        legend: { position: 'none' },
                        chartArea: { width: '50%' }
                    };

                    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
                    chart.draw(data, options);
                }
            </script>
        <?php endif; ?>
        <a href="index.html">Volver al formulario</a>
    </div>
</body>
</html>
</html>
