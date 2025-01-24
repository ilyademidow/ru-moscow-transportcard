<?php
/**
 * @author: Ilya Demidov
 * @source: https://github.com/ilyademidow/ru-moscow-transportcard
 * @date: 18.03.2016
 * @description: Simple Strelka Card Balance Checker. Monitors balance changes on Moscow transport card and sends email notifications
 **/

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Configuration
$config = [
    'my_email' => '',  // Your email address
    'my_card_number' => '03317937536', // Your Strelka card number
    'balance_file' => 'balance.txt', // File to store previous balance
    'card_type_id' => '3ae427a1-0f17-4524-acb1-a3f50090a8f3' // Default card type ID for all cards
];

// Get current balance from Strelka API
function getCardBalance($cardNumber, $cardTypeId) {
    $url = "https://strelkacard.ru/api/cards/status/?cardnum={$cardNumber}&cardtypeid={$cardTypeId}";
    $response = file_get_contents($url);
    $data = json_decode($response);
    return $data->balance;
}

// Format amount to rubles
function formatAmount($amount) {
    return number_format($amount/100, 2) . ' руб.';
}

// Send notification email
function sendNotification($to, $oldBalance, $newBalance) {
    $difference = abs($oldBalance - $newBalance);
    $isDebit = $oldBalance > $newBalance;
    
    $subject = $isDebit ? 'Списание со Стрелки' : 'Пополнение Стрелки';
    $action = $isDebit ? 'Списано' : 'Пополнено';
    
    $message = sprintf(
        '%s %s, текущий баланс %s', 
        $action,
        formatAmount($difference),
        formatAmount($newBalance)
    );
    
    mail($to, $subject, $message);
}

// MAIN LOGIC
try {
    // Get current balance
    $currentBalance = getCardBalance($config['my_card_number'], $config['card_type_id']);
    
    // Get previous balance from file
    $previousBalance = file_exists($config['balance_file']) 
        ? (int)file_get_contents($config['balance_file']) 
        : $currentBalance;
    
    // Check if balance changed
    if ($previousBalance !== $currentBalance) {
        // Send notification if email is configured
        if (!empty($config['my_email'])) {
            sendNotification($config['my_email'], $previousBalance, $currentBalance);
        }
        
        // Save new balance
        file_put_contents($config['balance_file'], $currentBalance);
    }
    
} catch (Exception $e) {
    error_log("Error checking Strelka balance: " . $e->getMessage());
}
