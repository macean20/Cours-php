<?php

// Fonctions d'accès aux données
function selectClients(): array {
    return [
        [
            "nom" => "Wane",
            "prenom" => "Baila",
            "telephone" => "777661010",
            "adresse" => "FO",
            "dettes" => []
        ],
        [
            "nom" => "Wane1",
            "prenom" => "Baila1",
            "telephone" => "777661011",
            "adresse" => "FO1",
            "dettes" => [
                [
                    "montdette" => 5000,
                    "datepret" => "12-10-2012",
                    "echeance" => "12-10-2023",
                    "ref" => "1234",
                    "montverse" => 2500,
                    "paiement" => [
                        [
                            "ref" => "1235",
                            "date" => "12-12-2012",
                            "montantpaie" => 2500
                        ],
                        [
                            "ref" => "123",
                            "date" => "12-11-2015",
                            "montantpaie" => 2500
                        ]
                    ]
                ]
            ]
        ]
    ];
}

function selectClientByTel(array $clients, string $tel): array|null {
    foreach ($clients as $client) {
        if ($client["telephone"] === $tel) {
            return $client;
        }
    }
    return null;
}

function insertClient(array &$tabClients, array $client): void {
    $tabClients[] = $client;
}

// Fonctions métier
function enregistrerClient(array &$tabClients, array $client): bool {
    $result = selectClientByTel($tabClients, $client["telephone"]);
    if ($result === null) {
        insertClient($tabClients, $client);
        return true;
    }
    return false;
}

function listerClients(array $clients): void {
    if (count($clients) === 0) {
        echo "Pas de clients à afficher.\n";
    } else {
        foreach ($clients as $client) {
            echo "\n-----------------------------------------\n";
            echo "Téléphone : " . $client["telephone"] . "\t";
            echo "Nom : " . $client["nom"] . "\t";
            echo "Prénom : " . $client["prenom"] . "\t";
            echo "Adresse : " . $client["adresse"] . "\n";
        }
    }
}

function estVide(string $value): bool {
    return empty($value);
}

function telephoneIsUnique(array $clients, string $sms): string {
    do {
        $value = readline($sms);
    } while (estVide($value) || selectClientByTel($clients, $value) !== null);
    return $value;
}

function saisieChampObligatoire(string $sms): string {
    do {
        $value = readline($sms);
    } while (estVide($value));
    return $value;
}

function saisieClient(array $clients): array {
    return [
        "telephone" => telephoneIsUnique($clients, "Entrer le téléphone : "),
        "nom" => saisieChampObligatoire("Entrer le nom : "),
        "prenom" => saisieChampObligatoire("Entrer le prénom : "),
        "adresse" => saisieChampObligatoire("Entrer l'adresse : "),
        "dettes" => []
    ];
}

function verifMontant(string $sms): float {
    do {
        $montant = (float)readline($sms);
    } while ($montant <= 0);
    return $montant;
}

function saisieDette(): array {
    return [
        "montdette" => verifMontant("Entrer le montant de la dette : "),
        "datepret" => saisieChampObligatoire("Entrer la date du prêt : "),
        "echeance" => saisieChampObligatoire("Entrer la date d'échéance : "),
        "ref" => uniqid(),
        "montverse" => verifMontant("Entrer le montant versé : "),
        "paiement" => []
    ];
}

function insertDette(array &$clients, int $index, array $dette): void {
    $clients[$index]["dettes"][] = $dette;
}

function indexClientByTel(array $clients, string $tel): int {
    foreach ($clients as $index => $client) {
        if ($client["telephone"] === $tel) {
            return $index;
        }
    }
    return -1;
}

function listerDettesByClient(string $numero, array $dettes): void {
    foreach ($dettes as $dette) {
        echo "\n-----------------------------------------\n";
        echo "Téléphone : " . $numero . "\t";
        echo "Montant de la dette : " . $dette["montdette"] . "\t";
        echo "Date du prêt : " . $dette["datepret"] . "\t";
        echo "Date d'échéance : " . $dette["echeance"] . "\t";
        echo "Référence : " . $dette["ref"] . "\t";
        echo "Montant versé : " . $dette["montverse"] . "\n";
    }
}

function payerDette(array &$dette): void {
    echo "\nPaiement d'une dette\n";
    $montantPaye = verifMontant("Entrez le montant à payer : ");
    $montantRestant = $dette['montdette'] - $dette['montverse'];

    if ($montantPaye > $montantRestant) {
        echo "Montant de paiement supérieur au montant restant.\n";
    } else {
        $dette['montverse'] += $montantPaye;
        $dette['paiement'][] = [
            "ref" => uniqid(),
            "date" => date("d-m-Y"),
            "montantpaie" => $montantPaye
        ];
        echo "Paiement enregistré avec succès. Montant restant : " . ($montantRestant - $montantPaye) . "\n";
    }
}

function menu(): int {
    echo "\nMenu :\n";
    echo "1. Ajouter un client\n";
    echo "2. Lister les clients\n";
    echo "3. Ajouter une dette\n";
    echo "4. Afficher les dettes d'un client\n";
    echo "5. Payer une dette\n";
    echo "6. Quitter\n";
    return (int)readline("Faites votre choix : ");
}

// Fonction principale
function principal() {
    $clients = selectClients();
    do {
        $choix = menu();
        switch ($choix) {
            case 1:
                $client = saisieClient($clients);
                if (enregistrerClient($clients, $client)) {
                    echo "Client enregistre avec success.\n";
                } else {
                    echo "Le numero de télephone existe déja.\n";
                }
                break;
            case 2:
                listerClients($clients);
                break;
            case 3:
                $tel = readline("Entrer le numéro de telephone : ");
                $index = indexClientByTel($clients, $tel);
                if ($index !== -1) {
                    $dette = saisieDette();
                    insertDette($clients, $index, $dette);
                    echo "Dette ajoutee avec succes.\n";
                } else {
                    echo "Numéro de téléphone introuvable.\n";
                }
                break;
            case 4:
                $tel = readline("Entrer le numéro de téléphone : ");
                $client = selectClientByTel($clients, $tel);
                if ($client !== null) {
                    listerDettesByClient($tel, $client["dettes"]);
                } else {
                    echo "Numéro de téléphone introuvable.\n";
                }
                break;
            case 5:
                $tel = readline("Entrez le numéro de téléphone du client : ");
                $index = indexClientByTel($clients, $tel);
                if ($index !== -1) {
                    $dettes = &$clients[$index]['dettes'];
                    if (count($dettes) > 0) {
                        echo "Liste des dettes :\n";
                        foreach ($dettes as $key => $dette) {
                            echo "$key : Montant : " . $dette['montdette'] . ", Montant versé : " . $dette['montverse'] . "\n";
                        }
                        $choixDette = (int)readline("Entrez l'indice de la dette à payer : ");
                        if (isset($dettes[$choixDette])) {
                            payerDette($dettes[$choixDette]);
                        } else {
                            echo "montant de la dette invalide.\n";
                        }
                    } else {
                        echo "Aucune dette enregistree pour ce client.\n";
                    }
                } else {
                    echo "Numero de téléphone introuvable.\n";
                }
                break;
            case 6:
                echo "Au revoir !\n";
                break;
            default:
                echo "Choix invalide, veuillez réessayer.\n";
        }
    } while ($choix !== 6);
}

principal();
?>