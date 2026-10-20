<?php
function normalizarTextoChat($texto) {
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    $mapaTildes = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
        'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
    ];
    $texto = strtr($texto, $mapaTildes);
    return trim(preg_replace('/\s+/', ' ', $texto));
}

function extraerFiltros($consultaOriginal) {
    $norm = normalizarTextoChat($consultaOriginal);
    $filtros = [];

    // 1. Estado
    if (preg_match('/\b(disponibles?)\b/u', $norm)) {
        $filtros['estado'] = 'Disponible';
    } elseif (preg_match('/\b(en\s+mantenimiento|mantenimiento|taller)\b/u', $norm)) {
        $filtros['estado'] = 'En mantenimiento';
    } elseif (preg_match('/\b(trabajando|en\s+obra|obra)\b/u', $norm)) {
        $filtros['estado'] = 'Trabajando';
    }

    // 2. Categoría
    if (preg_match('/\b(maquinarias?|maquinas?)\b/u', $norm)) {
        $filtros['categoria'] = 'Maquinaria';
    } elseif (preg_match('/\b(vehiculos?|carros?|camionetas?|autos?|automoviles?)\b/u', $norm)) {
        $filtros['categoria'] = 'Vehiculo';
    }

    // 3. Modelo (del texto original)
    if (preg_match('/\b(?:modelos?|referencias?)\s+([a-zA-Z0-9\-_]+(?:\s+[a-zA-Z0-9\-_]+)*?)(?=\s+(?:hay|estan?|en\b|disponibles?|trabajando|mantenimiento|de\b)|$|[,\.?!])/iu', $consultaOriginal, $mMod)) {
        $valMod = trim($mMod[1]);
        if (!empty($valMod)) {
            $filtros['modelo'] = $valMod;
        }
    }

    // 4. Ubicación (del texto original)
    // Detectar "ubicacion X" o "en X"
    if (preg_match('/\b(?:ubicacion(?:es)?|ubicacion\s+actual)\s+([a-zA-Z0-9\-_]+(?:\s+[a-zA-Z0-9\-_]+)*?)(?=\s+(?:hay|estan?|disponibles?|trabajando|mantenimiento)|$|[,\.?!])/iu', $consultaOriginal, $mUbic)) {
        $valUbic = trim($mUbic[1]);
        if (!empty($valUbic)) {
            $filtros['ubicacion_actual'] = $valUbic;
        }
    } elseif (preg_match('/\ben\s+([a-zA-Z0-9\-_]+(?:\s+[a-zA-Z0-9\-_]+)*?)(?=\s+(?:hay|estan?|disponibles?|trabajando|mantenimiento)|$|[,\.?!])/iu', $consultaOriginal, $mEn)) {
        $valEn = trim($mEn[1]);
        $valEnNorm = normalizarTextoChat($valEn);
        // Excluir estados o frases comunes que usan "en"
        $ignorarEn = ['mantenimiento', 'obra', 'taller', 'el inventario', 'inventario', 'total', 'la base'];
        if (!empty($valEn) && !in_array($valEnNorm, $ignorarEn)) {
            $filtros['ubicacion_actual'] = $valEn;
        }
    }

    return $filtros;
}

$pruebas = [
    "cuántas máquinas hay disponibles",
    "cuántos en mantenimiento",
    "cuántos modelos WIWI hay disponibles",
    "cuántos hay en MIA",
    "cuántos vehículos en RIVERA trabajando",
    "cuántos hay en total",
    "eliminar modelo WIWI",
    "eliminar en mantenimiento",
    "eliminar en MIA",
    "eliminar maquinaria en RIVERA",
];

foreach ($pruebas as $p) {
    echo "Query: $p\n";
    $f = extraerFiltros($p);
    echo "Filtros: " . json_encode($f) . "\n\n";
}
