<?php
   header('Content-Type: application/json');
   $usersFolder = 'users/';
   $userFiles = glob($usersFolder . 'id_*.json');
   $players = [];

   foreach ($userFiles as $file) {
       $jsonData = file_get_contents($file);
       $userData = json_decode($jsonData, true);

       if ($userData && isset($userData['price'])) {
           $players[] = [
               'username' => $userData['username'],
               'nickname' => $userData['nickname'],
               'telegram_id' => $userData['telegram_id'],
               'price' => $userData['price']
           ];
       }
   }

   usort($players, function($a, $b) {
       return $b['price'] <=> $a['price'];
   });

   $topPlayers = array_slice($players, 0, 100);
   echo json_encode($topPlayers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
   ?>
