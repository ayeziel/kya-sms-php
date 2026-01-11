# KYA SMS PHP SDK

[![Latest Version](https://img.shields.io/packagist/v/kyasms/kya-sms-php.svg)](https://packagist.org/packages/kyasms/kya-sms-php)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

SDK PHP officiel pour l'API KYA SMS. Envoyez des SMS, OTP et gérez vos campagnes facilement.

## 📋 Table des matières

- [Installation](#installation)
- [Configuration](#configuration)
- [SMS API](#sms-api)
  - [Envoyer un SMS](#envoyer-un-sms)
  - [Statut des messages](#statut-des-messages)
  - [Historique SMS](#historique-sms)
- [OTP API](#otp-api)
  - [Envoyer un OTP](#envoyer-un-otp)
  - [Vérifier un OTP](#vérifier-un-otp)
- [Campaign API](#campaign-api)
  - [Créer une campagne](#créer-une-campagne)
  - [Statut d'une campagne](#statut-dune-campagne)
  - [Historique des campagnes](#historique-des-campagnes)
  - [Calculer le coût](#calculer-le-coût)
- [Gestion des erreurs](#gestion-des-erreurs)
- [License](#license)

---

## Installation

```bash
composer require kyasms/kya-sms-php
```

### Prérequis

- PHP 8.0 ou supérieur
- Extension JSON
- Guzzle HTTP Client 7.0+

---

## Configuration

### Serveurs API

| Serveur | URL | Description |
|---------|-----|-------------|
| **Principal** | `https://route.kyasms.com/api/v3` | Serveur par défaut |
| **Secours** | `https://route.kyasms.net/api/v3` | Serveur backup |

### Initialisation

```php
<?php
require_once 'vendor/autoload.php';

use KyaSms\KyaSms;

// Méthode 1 : Simple (recommandée)
$client = new KyaSms('votre-cle-api');

// Méthode 2 : Avec URL personnalisée (serveur de secours)
$client = new KyaSms('votre-cle-api', 'https://route.kyasms.net/api/v3');

// Méthode 3 : Avec options
$client = new KyaSms('votre-cle-api', [
    'timeout' => 60,
    'debug' => true,
]);

// Méthode 4 : Configuration complète
$client = new KyaSms([
    'api_key' => 'votre-cle-api',
    'base_url' => 'https://route.kyasms.com/api/v3',
    'timeout' => 30,
    'connect_timeout' => 10,
    'debug' => false,
]);

// Méthode 5 : Variables d'environnement
// Définir: KYA_SMS_API_KEY=votre-cle-api
$client = KyaSms::fromEnvironment();
```

---

## SMS API

### Envoyer un SMS

**Endpoint:** `POST /sms/send`

#### Envoi simple

```php
<?php
use KyaSms\KyaSms;

$client = new KyaSms('votre-cle-api');

try {
    $response = $client->sms()->sendSimple(
        'MonApp',           // Sender ID (max 11 caractères)
        '22990123456',      // Numéro destinataire
        'Bonjour! Ceci est un test.'  // Message
    );

    if ($response->isSuccess()) {
        echo "✅ SMS envoyé!\n";
        echo "Message ID: " . $response->getMessageId() . "\n";
        echo "Statut: " . $response->getStatus() . "\n";
        echo "Route: " . $response->getRoute() . "\n";
        echo "Prix: " . $response->getPrice() . " XOF\n";
        echo "Parties SMS: " . $response->getSmsPart() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

#### Envoi à plusieurs destinataires

```php
$response = $client->sms()->sendSimple(
    'MonApp',
    ['22990123456', '22991234567', '22992345678'],  // Tableau de numéros
    'Message pour tout le monde!'
);

// Récupérer tous les IDs de messages
$messageIds = $response->getMessageIds();
print_r($messageIds);
// Array ( [0] => "abc123", [1] => "def456", [2] => "ghi789" )

// Récupérer le coût total
$totalPrice = $response->getTotalPrice();
echo "Coût total: {$totalPrice} XOF\n";

// Parcourir tous les messages
foreach ($response->getData() as $msg) {
    echo "ID: {$msg['messageId']}, To: {$msg['to']}, Status: {$msg['status']}\n";
}

// Ou avec une chaîne séparée par des virgules
$response = $client->sms()->sendSimple(
    'MonApp',
    '22990123456,22991234567,22992345678',
    'Message pour tout le monde!'
);
```

#### Méthodes SmsResponse disponibles

| Méthode | Description |
|---------|-------------|
| `isSuccess()` | Retourne `true` si l'envoi a réussi |
| `getMessageId()` | ID du premier message |
| `getMessageIds()` | Tableau de tous les IDs |
| `getStatus()` | Statut du premier message |
| `getRoute()` | Route du premier message (ex: "BJ(MTN)") |
| `getPrice()` | Prix du premier message |
| `getTotalPrice()` | Prix total de tous les messages |
| `getSmsPart()` | Nombre de segments du premier message |
| `getTo()` | Numéro du premier destinataire |
| `getMessage()` | Contenu du premier message |
| `getCreatedAt()` | Date de création |
| `getData()` | Tableau complet de tous les messages |
| `getFirstMessage()` | Données complètes du premier message |

#### Envoi Flash SMS

```php
// Le SMS s'affiche directement à l'écran sans être stocké
$response = $client->sms()->sendFlash(
    'Alerte',
    '22990123456',
    'URGENT: Votre code est 1234'
);
```

#### Envoi avec Template

```php
$response = $client->sms()->sendWithTemplate(
    from: 'MonApp',
    to: '22990123456',
    templateId: 'template-api-key',
    lang: 'fr'
);
```

#### Envoi Bulk (vers des groupes)

```php
// Envoyer à des groupes de contacts
$response = $client->sms()->sendBulk(
    from: 'MonApp',
    groupIds: ['groupe-id-1', 'groupe-id-2'],
    message: 'Bonjour {phone_name}! Voici nos offres du jour.'
);

// Bulk avec template
$response = $client->sms()->sendBulkWithTemplate(
    from: 'MonApp',
    groupIds: ['groupe-id-1'],
    templateId: 'promo-template',
    lang: 'fr'
);
```

#### Envoi avancé avec SmsMessage

```php
use KyaSms\Models\SmsMessage;

$message = SmsMessage::create('MonApp', '22990123456')
    ->setMessage('Votre code de vérification est: 123456')
    ->setType(SmsMessage::TYPE_TEXT)
    ->setWallet('principal');

$response = $client->sms()->send($message);
```

#### Réponse de succès

```json
{
    "reason": "success",
    "from": "MonApp",
    "wallet": "principal",
    "callback_url": "https://route.kyasms.com",
    "data": [
        {
            "messageId": "8248cd80e7fb7fbd8edffe",
            "status": "ACT",
            "to": "22990123456",
            "message": "Bonjour! Ceci est un test.",
            "route": "BJ(MTN)",
            "sms_part": 1,
            "price": 12,
            "created_at": "2024-11-25 17:24:09"
        }
    ]
}
```

---

### Statut des messages

**Endpoint:** `POST /message/status`

```php
<?php
use KyaSms\KyaSms;

$client = new KyaSms('votre-cle-api');

try {
    // Vérifier le statut de plusieurs messages (max 100)
    $statuses = $client->sms()->getStatus([
        '8248cd80e7fb7fbd8edffe',
        '4a5f6baf-3741-45a2-9821-df63d9b0f83f'
    ]);

    foreach ($statuses as $messageId => $status) {
        echo "Message: {$messageId}\n";
        echo "  Téléphone: {$status['phone']}\n";
        echo "  Statut: {$status['status']}\n";
        echo "  Route: {$status['route']}\n";
        echo "  Mis à jour: {$status['updated_at']}\n\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

#### Statut d'un seul message

```php
$status = $client->sms()->getMessageStatus('8248cd80e7fb7fbd8edffe');

if ($status) {
    echo "Statut: {$status['status']}\n";
}
```

#### Vérifier si livré

```php
if ($client->sms()->isDelivered('8248cd80e7fb7fbd8edffe')) {
    echo "✅ Message livré!\n";
} else {
    echo "⏳ En attente de livraison...\n";
}
```

#### États possibles

| Statut | Description |
|--------|-------------|
| `ACCEPTED` | Message reçu et validé par le système |
| `SENT` | Message transmis à l'opérateur |
| `DELIVERED` | Message livré au destinataire |
| `FAILED` | Échec de livraison |

#### Réponse de succès

```json
{
    "reason": "success",
    "data": {
        "8248cd80e7fb7fbd8edffe": {
            "phone": "22990123456",
            "status": "DELIVERED",
            "route": "BJ(MTN)",
            "updated_at": "2024-11-25 17:24:15"
        }
    }
}
```

---

### Historique SMS

**Endpoint:** `POST /sms/history`

⚠️ **Limites de performance:**
- `per_page` max: 100 (défaut: 50)
- Plage de dates max: 31 jours
- Si pas de dates spécifiées: 30 derniers jours automatiquement

```php
<?php
use KyaSms\KyaSms;

$client = new KyaSms('votre-cle-api');

try {
    // Récupérer l'historique récent (30 derniers jours par défaut)
    $history = $client->sms()->getHistory();

    // Avec filtres de date
    $history = $client->sms()->getHistory(
        startDate: '2026-01-01',
        endDate: '2026-01-31',
        page: 1,
        perPage: 50  // max 100
    );

    echo "=== Historique SMS ===\n\n";

    foreach ($history['messages'] as $msg) {
        echo "ID: {$msg['messageId']}\n";
        echo "  De: {$msg['from']}\n";
        echo "  À: {$msg['to']}\n";
        echo "  Message: {$msg['message']}\n";
        echo "  Statut: {$msg['status']}\n";
        echo "  Route: {$msg['route']}\n";
        echo "  Prix: {$msg['price']} XOF\n";
        echo "  Date: {$msg['created_at']}\n\n";
    }

    // Pagination
    $pagination = $history['pagination'];
    echo "Page {$pagination['current_page']} / {$pagination['total_pages']}\n";
    echo "Total: {$pagination['total_records']} messages\n";

    if ($pagination['has_more']) {
        echo "Plus de messages disponibles...\n";
    }
    
    // Vérifier si les résultats sont limités (>10000 records)
    if ($pagination['records_limited'] ?? false) {
        echo "⚠️ Plus de 10000 résultats. Utilisez des filtres.\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

#### Réponse de succès

```json
{
    "reason": "success",
    "data": {
        "messages": [
            {
                "messageId": "8248cd80e7fb7fbd8edffe",
                "to": "22990123456",
                "from": "MonApp",
                "message": "Test message",
                "status": "DELIVERED",
                "route": "BJ(MTN)",
                "sms_parts": 1,
                "price": 12,
                "created_at": "2024-01-15 10:30:00",
                "updated_at": "2024-01-15 10:30:05"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 50,
            "total_pages": 5,
            "total_records": 230,
            "has_more": true
        }
    }
}
```

---

## OTP API

### Envoyer un OTP

**Endpoint:** `POST /otp/create`

#### Envoi simple

```php
<?php
use KyaSms\KyaSms;

$client = new KyaSms('votre-cle-api');

try {
    $response = $client->otp()->send(
        appId: 'votre-app-id',      // ID de l'application OTP
        recipient: '22990123456',    // Numéro de téléphone
        lang: 'fr'                   // Langue (fr, en, es, de)
    );

    if ($response->isSuccess()) {
        $otpKey = $response->getKey();
        
        echo "✅ OTP envoyé!\n";
        echo "Clé de vérification: {$otpKey}\n";
        
        // IMPORTANT: Stocker cette clé pour la vérification
        // $_SESSION['otp_key'] = $otpKey;
        // Ou en base de données
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

#### OTP avec code personnalisé

```php
// Générer votre propre code
$monCode = (string) random_int(100000, 999999);

$response = $client->otp()->sendWithCustomCode(
    appId: 'votre-app-id',
    recipient: '22990123456',
    code: $monCode,
    lang: 'fr',
    minutes: 5  // Expire dans 5 minutes
);

echo "Code envoyé: {$monCode}\n";
echo "Clé: " . $response->getKey() . "\n";
```

#### OTP avec durée d'expiration

```php
$response = $client->otp()->sendWithExpiration(
    appId: 'votre-app-id',
    recipient: '22990123456',
    minutes: 10,  // Valide 10 minutes
    lang: 'fr'
);
```

#### OTP avancé avec OtpRequest

```php
use KyaSms\Models\OtpRequest;

$request = OtpRequest::create('votre-app-id', '22990123456', 'fr')
    ->setCode('123456')
    ->setMinutes(15);

$response = $client->otp()->create($request);
```

#### Réponse de succès

```json
{
    "reason": "success",
    "key": "75673c3d-618a-4f4b-a18c-40590f605d30"
}
```

---

### Vérifier un OTP

**Endpoint:** `POST /otp/verify`

```php
<?php
use KyaSms\KyaSms;

$client = new KyaSms('votre-cle-api');

try {
    // Récupérer la clé stockée et le code saisi par l'utilisateur
    $otpKey = '75673c3d-618a-4f4b-a18c-40590f605d30';  // Stockée lors de l'envoi
    $codeUtilisateur = '123456';  // Saisi par l'utilisateur

    $result = $client->otp()->verify(
        appId: 'votre-app-id',
        key: $otpKey,
        code: $codeUtilisateur
    );

    if ($client->otp()->isVerified($result)) {
        echo "✅ OTP vérifié avec succès!\n";
        echo "L'utilisateur est authentifié.\n";
        
        // Autoriser la connexion, valider l'action, etc.
        
    } else {
        echo "❌ Vérification échouée!\n";
        echo "Status: {$result['status']}\n";
        echo "Message: {$result['msg']}\n";
        
        // Gérer l'erreur spécifique
        switch ($result['status']) {
            case 100:
                echo "→ Clé de vérification invalide\n";
                break;
            case 101:
                echo "→ Nombre max de tentatives atteint ou IP changée\n";
                break;
            case 102:
                echo "→ Code incorrect\n";
                break;
            case 103:
                echo "→ Code expiré - demandez un nouveau code\n";
                break;
        }
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

#### Codes de statut OTP

| Status | Message | Description |
|--------|---------|-------------|
| 200 | checked | OTP vérifié avec succès |
| 100 | Validation mistake: key | Clé de vérification invalide |
| 101 | Number of attempts reached | Tentatives épuisées ou IP changée |
| 102 | Invalid authentication code | Code incorrect |
| 103 | Authentication code expired | Code expiré |

#### Réponse de succès

```json
{
    "reason": "success",
    "status": 200,
    "msg": "checked"
}
```

#### Flux complet d'authentification OTP

```php
<?php
use KyaSms\KyaSms;

$client = new KyaSms('votre-cle-api');

// ========== ÉTAPE 1: Envoyer l'OTP ==========
function envoyerOtp($client, $telephone) {
    $response = $client->otp()->send('app-id', $telephone, 'fr');
    
    if ($response->isSuccess()) {
        // Stocker la clé en session
        $_SESSION['otp_key'] = $response->getKey();
        $_SESSION['otp_phone'] = $telephone;
        return true;
    }
    return false;
}

// ========== ÉTAPE 2: Vérifier l'OTP ==========
function verifierOtp($client, $code) {
    $otpKey = $_SESSION['otp_key'] ?? null;
    
    if (!$otpKey) {
        return ['success' => false, 'error' => 'Session expirée'];
    }
    
    $result = $client->otp()->verify('app-id', $otpKey, $code);
    
    if ($client->otp()->isVerified($result)) {
        // Nettoyer la session
        unset($_SESSION['otp_key']);
        unset($_SESSION['otp_phone']);
        
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => $result['msg']];
}

// Utilisation
if (envoyerOtp($client, '22990123456')) {
    echo "OTP envoyé! Vérifiez votre téléphone.\n";
}

// Plus tard, quand l'utilisateur soumet le code
$resultat = verifierOtp($client, '123456');
if ($resultat['success']) {
    echo "Authentification réussie!\n";
} else {
    echo "Erreur: {$resultat['error']}\n";
}
```

---

## Campaign API

### Créer une campagne

**Endpoint:** `POST /sms/campaign/create`

#### Campagne automatique (immédiate)

```php
<?php
use KyaSms\KyaSms;

$client = new KyaSms('votre-cle-api');

try {
    $response = $client->campaign()->createAutomatic(
        name: 'Promo Flash',
        groups: ['groupe-id-1', 'groupe-id-2'],
        senderId: 'MonApp',
        message: 'Profitez de -50% aujourd\'hui seulement!'
    );

    if ($response->isSuccess()) {
        echo "✅ Campagne créée!\n";
        echo "ID: " . $response->getCampaignId() . "\n";
        echo "Statut: " . $response->getStatus() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

#### Campagne planifiée

```php
$response = $client->campaign()->createScheduled(
    name: 'Promo Noël 2024',
    groups: ['clients-vip'],
    senderId: 'MonApp',
    message: 'Joyeux Noël! Profitez de -20% avec le code NOEL2024',
    scheduleDate: '2024-12-25 08:00:00',
    timezone: 'Africa/Porto-Novo'
);

echo "Campagne programmée pour le 25 décembre!\n";
```

#### Campagne périodique

```php
use KyaSms\Models\Campaign;

$response = $client->campaign()->createPeriodic(
    name: 'Newsletter Hebdomadaire',
    groups: ['abonnes'],
    senderId: 'MonApp',
    message: 'Voici les nouveautés de la semaine!',
    periodic: Campaign::PERIODIC_WEEKLY_START,  // Début de semaine
    timezone: 'Africa/Porto-Novo'
);
```

**Types périodiques disponibles:**

| Constante | Description |
|-----------|-------------|
| `PERIODIC_WEEKLY_START` | Début de la semaine |
| `PERIODIC_WEEKLY_END` | Fin de la semaine |
| `PERIODIC_MONTHLY_START` | Début du mois |
| `PERIODIC_MONTHLY_END` | Fin du mois |
| `PERIODIC_SPECIFIC_DAY` | Jour spécifique du mois |
| `PERIODIC_BEGINNING_YEAR` | 1er janvier |
| `PERIODIC_CHRISTMAS` | 25 décembre |

#### Campagne avec template

```php
$response = $client->campaign()->createWithTemplate(
    name: 'Anniversaires du mois',
    groups: ['anniversaires-janvier'],
    senderId: 'MonApp',
    templateId: 'happy-birthday-template',
    templateLang: 'fr'
);
```

#### Campagne avancée avec Campaign model

```php
use KyaSms\Models\Campaign;

$campaign = Campaign::create('Ma Campagne', ['groupe-1', 'groupe-2'], 'MonApp')
    ->asScheduled('2024-12-31 23:59:00', 'Africa/Porto-Novo')
    ->setMessage('Bonne année {phone_name}!')
    ->setSmsType(Campaign::SMS_TYPE_TEXT);

$response = $client->campaign()->create($campaign);
```

#### Variables dynamiques

| Variable | Description |
|----------|-------------|
| `{phone_name}` | Nom du contact |
| `{phone_email}` | Email du contact |
| `{phone_custom1}` | Champ personnalisé 1 |
| `{phone_custom2}` | Champ personnalisé 2 |

```php
$client->campaign()->createAutomatic(
    name: 'Message personnalisé',
    groups: ['clients'],
    senderId: 'MonApp',
    message: 'Bonjour {phone_name}! Merci pour votre fidélité.'
);
```

#### Réponse de succès

```json
{
    "reason": "success",
    "campaign_id": "camp_abc123",
    "status": "pending",
    "scheduled_at": "2024-12-25 08:00:00"
}
```

---

### Statut d'une campagne

**Endpoint:** `GET /sms/campaign/status/{id}`

```php
<?php
use KyaSms\KyaSms;

$client = new KyaSms('votre-cle-api');

try {
    $campaignId = 'camp_abc123';
    
    $status = $client->campaign()->getStatus($campaignId);

    echo "=== Statut de la campagne ===\n";
    echo "ID: " . $status->getCampaignId() . "\n";
    echo "Statut: " . $status->getStatus() . "\n";
    
    $progress = $status->getProgress();
    if ($progress) {
        echo "Progression: {$progress['sent']} / {$progress['total']}\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

#### Vérifier la progression

```php
// Obtenir le pourcentage de progression
$progress = $client->campaign()->getProgress($campaignId);
echo "Progression: {$progress}%\n";

// Vérifier si terminée
if ($client->campaign()->isCompleted($campaignId)) {
    echo "✅ Campagne terminée!\n";
} else {
    echo "⏳ Campagne en cours...\n";
}
```

#### Boucle de suivi

```php
$campaignId = 'camp_abc123';

while (!$client->campaign()->isCompleted($campaignId)) {
    $progress = $client->campaign()->getProgress($campaignId);
    echo "Progression: {$progress}%\n";
    sleep(5);  // Attendre 5 secondes
}

echo "✅ Campagne terminée!\n";
```

---

### Historique des campagnes

**Endpoint:** `GET /sms/campaign/records`

⚠️ **Limites de performance:**
- `per_page` max: 50 (défaut: 20)
- Statistiques optionnelles via `include_stats`

```php
<?php
use KyaSms\KyaSms;

$client = new KyaSms('votre-cle-api');

try {
    $records = $client->campaign()->getRecords(1, 20);

    echo "=== Historique des campagnes ===\n\n";

    foreach ($records['campaigns'] as $campaign) {
        echo "Nom: {$campaign['name']}\n";
        echo "  ID: {$campaign['id']}\n";
        echo "  Type: {$campaign['type']}\n";
        echo "  Statut: {$campaign['status']}\n";
        echo "  Sender: {$campaign['sender']}\n";
        echo "  Créée le: {$campaign['created_at']}\n";
        
        // Statistiques SMS
        if (isset($campaign['stats'])) {
            echo "  --- Stats ---\n";
            echo "  Envoyés: {$campaign['stats']['total_sent']}\n";
            echo "  Délivrés: {$campaign['stats']['delivered']}\n";
            echo "  Échoués: {$campaign['stats']['failed']}\n";
            echo "  En attente: {$campaign['stats']['pending']}\n";
            echo "  Coût total: {$campaign['stats']['total_cost']} XOF\n";
            echo "  Taux livraison: {$campaign['stats']['delivery_rate']}%\n";
        }
        echo "\n";
    }

    // Pagination
    $pagination = $records['pagination'];
    echo "Page {$pagination['current_page']} / {$pagination['total_pages']}\n";
    echo "Total: {$pagination['total_records']} campagnes\n";

    if ($pagination['has_more']) {
        echo "Plus de campagnes disponibles...\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

#### Réponse de succès

```json
{
    "reason": "success",
    "data": {
        "campaigns": [
            {
                "id": 61,
                "name": "Anniversaires du mois",
                "type": "automatic",
                "status": "executed",
                "sender": "KYA SMS",
                "sms_type": "Plain Text",
                "is_template": true,
                "template_id": 31,
                "template_name": "Remerciement pour l'inscription",
                "execution_date": "2026-01-11 13:43:46",
                "schedule_type": null,
                "timezone": null,
                "sms_content": "Merci {phone_name}!...",
                "groups": [
                    {"id": "E6A510FB", "name": "Clients VIP"}
                ],
                "stats": {
                    "total_sent": 150,
                    "delivered": 142,
                    "failed": 3,
                    "pending": 5,
                    "total_cost": 1800.00,
                    "total_sms_parts": 150,
                    "delivery_rate": 94.67
                },
                "created_at": "2026-01-11 12:43:46",
                "updated_at": "2026-01-11 12:44:02"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total_pages": 3,
            "total_records": 61,
            "has_more": true
        }
    }
}
```

---

### Calculer le coût

**Endpoint:** `POST /sms/campaign/calculate-cost`

⚠️ **Limites de performance:**
- Max 20 groupes par requête
- Max 5000 contacts traités (estimation au-delà)
- Message max 1600 caractères

Calcule le coût estimé d'une campagne en utilisant les tarifs réels par pays/opérateur.

```php
<?php
use KyaSms\KyaSms;

$client = new KyaSms('votre-cle-api');

try {
    $cost = $client->campaign()->calculateCost(
        groups: ['E6A510FB', 'AUTRE_GROUPE'],
        message: 'Bonjour {phone_name}! Votre code: {phone_custom1}. Contact: {phone_email}'
    );

    echo "=== Estimation du coût ===\n";
    echo "Coût estimé: {$cost['estimated_cost']} XOF\n";
    echo "Destinataires totaux: {$cost['total_recipients']}\n";
    echo "Destinataires valides: {$cost['valid_recipients']}\n";
    echo "Contacts invalides: {$cost['invalid_contacts']}\n";
    echo "Total segments SMS: {$cost['total_sms_parts']}\n";
    echo "Moyenne segments/contact: {$cost['average_sms_parts']}\n";
    
    // Infos sur le message
    $msgInfo = $cost['message_info'];
    echo "\n--- Info message ---\n";
    echo "Encodage: {$msgInfo['encoding']}\n";
    echo "Caractères utilisés: {$msgInfo['characters_used']}\n";
    echo "Caractères par SMS: {$msgInfo['characters_per_message']}\n";
    echo "Segments de base: {$msgInfo['base_sms_parts']}\n";
    echo "Variables dynamiques: " . ($msgInfo['has_dynamic_variables'] ? 'Oui' : 'Non') . "\n";
    
    // Breakdown par pays/opérateur
    echo "\n--- Répartition par pays/opérateur ---\n";
    foreach ($cost['country_breakdown'] as $breakdown) {
        echo "{$breakdown['country']}({$breakdown['operator']}): ";
        echo "{$breakdown['contacts']} contacts, ";
        echo "{$breakdown['sms_parts']} SMS, ";
        echo "{$breakdown['cost']} XOF ";
        echo "({$breakdown['price_per_sms']} XOF/SMS)\n";
    }
    
    // Infos groupes
    echo "\n--- Groupes ---\n";
    foreach ($cost['groups_info'] as $group) {
        echo "- {$group['name']} ({$group['id']}): {$group['contact_count']} contacts\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

#### Réponse de succès

```json
{
    "reason": "success",
    "data": {
        "estimated_cost": 1560.00,
        "total_recipients": 100,
        "valid_recipients": 97,
        "invalid_contacts": 3,
        "total_sms_parts": 130,
        "average_sms_parts": 1.34,
        "message_info": {
            "encoding": "GSM_7BIT",
            "characters_used": 85,
            "characters_per_message": 160,
            "base_sms_parts": 1,
            "has_dynamic_variables": true
        },
        "country_breakdown": [
            {
                "country": "BJ",
                "operator": "MTN",
                "contacts": 50,
                "sms_parts": 65,
                "cost": 780.00,
                "price_per_sms": 12.0
            },
            {
                "country": "BJ",
                "operator": "Moov",
                "contacts": 47,
                "sms_parts": 65,
                "cost": 780.00,
                "price_per_sms": 12.0
            }
        ],
        "groups_info": [
            {
                "id": "E6A510FB",
                "name": "Clients VIP",
                "contact_count": 75
            },
            {
                "id": "AUTRE_GROUPE",
                "name": "Newsletter",
                "contact_count": 25
            }
        ]
    }
}
```

#### Notes importantes

- Le coût est calculé contact par contact en utilisant les tarifs de `TarifSmsByUser`
- Les variables dynamiques (`{phone_name}`, etc.) sont remplacées pour chaque contact avant calcul
- Les contacts invalides (numéros mal formatés) sont comptabilisés mais exclus du coût
- L'encodage (GSM-7 ou UCS-2) affecte le nombre de caractères par segment
- **Si >5000 contacts**: estimation basée sur un échantillon (champ `is_estimate: true`)

---

## Gestion des erreurs

```php
<?php
use KyaSms\KyaSms;
use KyaSms\Exceptions\KyaSmsException;
use KyaSms\Exceptions\AuthenticationException;
use KyaSms\Exceptions\ValidationException;
use KyaSms\Exceptions\ApiException;

$client = new KyaSms('votre-cle-api');

try {
    $response = $client->sms()->sendSimple('MonApp', '22990123456', 'Test');
    
} catch (AuthenticationException $e) {
    // Clé API invalide ou manquante
    echo "❌ Erreur d'authentification: " . $e->getMessage() . "\n";
    echo "Vérifiez votre clé API!\n";
    
} catch (ValidationException $e) {
    // Paramètres invalides
    echo "❌ Erreur de validation: " . $e->getMessage() . "\n";
    foreach ($e->getErrors() as $field => $error) {
        echo "  - {$field}: {$error}\n";
    }
    
} catch (ApiException $e) {
    // Erreur API (solde insuffisant, rate limit, etc.)
    echo "❌ Erreur API [{$e->getStatusCode()}]: " . $e->getMessage() . "\n";
    
    switch ($e->getStatusCode()) {
        case 402:
            echo "→ Solde insuffisant. Rechargez votre compte.\n";
            break;
        case 429:
            echo "→ Trop de requêtes. Attendez un moment.\n";
            break;
        case 500:
        case 503:
            echo "→ Erreur serveur. Essayez le serveur de secours.\n";
            break;
    }
    
} catch (KyaSmsException $e) {
    // Autre erreur SDK
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

### Codes d'erreur HTTP

| Code | Description | Solution |
|------|-------------|----------|
| 400 | Requête invalide | Vérifiez les paramètres |
| 401 | Non authentifié | Ajoutez/vérifiez la clé API |
| 403 | Accès refusé | Compte désactivé ou permissions insuffisantes |
| 404 | Non trouvé | Vérifiez l'endpoint |
| 422 | Erreur de validation | Corrigez les paramètres |
| 429 | Rate limit | Attendez et réessayez |
| 402 | Solde insuffisant | Rechargez votre compte |
| 500 | Erreur serveur | Réessayez ou utilisez le serveur backup |
| 503 | Service indisponible | Utilisez `route.kyasms.net` |

### Failover automatique

```php
use KyaSms\KyaSms;
use KyaSms\Exceptions\ApiException;

$servers = [
    'https://route.kyasms.com/api/v3',
    'https://route.kyasms.net/api/v3',
];

$response = null;
$lastError = null;

foreach ($servers as $server) {
    try {
        $client = new KyaSms('votre-cle-api', $server);
        $response = $client->sms()->sendSimple('MonApp', '22990123456', 'Test');
        echo "✅ Envoyé via {$server}\n";
        break;
        
    } catch (ApiException $e) {
        $lastError = $e;
        if ($e->getStatusCode() >= 500) {
            echo "⚠️ Serveur {$server} indisponible, essai suivant...\n";
            continue;
        }
        throw $e;  // Erreur client, pas besoin de réessayer
    }
}

if (!$response && $lastError) {
    throw $lastError;
}
```

---

## Structure du projet

```
kya-sms-php/
├── src/
│   ├── KyaSms.php              # Client principal
│   ├── Api/
│   │   ├── SmsApi.php          # API SMS
│   │   ├── OtpApi.php          # API OTP
│   │   └── CampaignApi.php     # API Campagnes
│   ├── Models/
│   │   ├── SmsMessage.php
│   │   ├── SmsResponse.php
│   │   ├── OtpRequest.php
│   │   ├── OtpResponse.php
│   │   ├── Campaign.php
│   │   └── CampaignResponse.php
│   ├── Exceptions/
│   │   ├── KyaSmsException.php
│   │   ├── AuthenticationException.php
│   │   ├── ValidationException.php
│   │   └── ApiException.php
│   └── Http/
│       └── HttpClient.php
├── tests/
├── examples/
├── composer.json
├── README.md
└── LICENSE
```

---

## License

MIT License - voir [LICENSE](LICENSE)

## Support

- Documentation: [https://docs.kyasms.com](https://docs.kyasms.com)
- Dashboard: [https://app.kyasms.com](https://app.kyasms.com)
- Email: support@kyasms.com
