<?php
   
    $products = [];
    $cart = [];
    $profileName = '';
    $profileAge = 7;

    setlocale(LC_ALL, 'uk_UA.UTF-8'); 
    mb_internal_encoding('UTF-8');
    run($products, $cart, $profileName, $profileAge);

    function run($products, $cart, $profileName, $profileAge) {
        getProductData($products);
        getCartData($cart);

        while (true) {
            showMainMenu();
            $command = getInput();
            
            switch ($command) {
                case '1':
                    selectProducts($products, $cart);
                    break;
                case '2':
                    showReceipt($cart);
                    break;
                case '3':
                    setupProfile($profileName, $profileAge);
                    break;
                case '0':
                    exit;
                default:
                    showError();
            }
        }
    }

    function showMainMenu() {
        echo "\n################################\n";
        echo "# ПРОДОВОЛЬЧИЙ МАГАЗИН \"ВЕСНА\" #\n";
        echo "################################\n";
        echo "1 Вибрати товари\n";
        echo "2 Отримати підсумковий рахунок\n";
        echo "3 Налаштувати свій профіль\n";
        echo "0 Вийти з програми\n";
        echo "Введіть команду: ";
    }

    function showError() {
        echo "ПОМИЛКА! Введіть правильну команду\n";
        echo "1 Вибрати товари\n";
        echo "2 Отримати підсумковий рахунок\n";
        echo "3 Налаштувати свій профіль\n";
        echo "0 Вийти з програми\n";
        echo "Введіть команду: ";
    }

     function selectProducts(&$products, &$cart) {

        while (true) {

            showProducList($products);
            echo "\n";
           
            echo "Виберіть товар: ";
            $productId = getInput();
            
            if ($productId === '0') {
                break;
            }
            
            if (!isset($products[$productId])) {
                echo "ПОМИЛКА! ВКАЗАНО НЕПРАВИЛЬНИЙ НОМЕР ТОВАРУ\n\n";
                continue;
            }
            
            $product = $products[$productId];
            echo "Вибрано: " . $product['name'] . "\n";
            echo "Введіть кількість, штук: ";
            $quantity = getInput();
            
            if (!is_numeric($quantity) || $quantity < 0 || $quantity >= 100) {
                echo "ПОМИЛКА! Введіть коректну кількість (0-99)\n";
                continue;
            }
            
            $quantity = (int)$quantity;
            
            if ($quantity === 0) {
                if (isset($cart[$productId])) {
                    unset($cart[$productId]);
                    echo "ВИДАЛЯЮ З КОШИКА\n";
                    updateCartData($cart);
                }
            } else {
                if(isset($cart[$productId])) {
                    $quantity += $cart[$productId]['quantity'];
                }
                $cart[$productId] = [
                   'name' => $product['name'],
                   'price' => $product['price'],
                   'quantity' => $quantity
                ];

                updateCartData($cart);
            }
            
            showCart($cart);
        }
    }

    function showProducList($products) {
        $maxNameLength = mb_strlen("НАЗВА");
        $maxPriceLength =  mb_strlen("ЦІНА");

        echo "\n";
        foreach ($products as  $id => $product) {
            if(mb_strlen($product['name']) > $maxNameLength) {
                $maxNameLength = mb_strlen($product['name']); 
            }  
        } 

        foreach ($products as  $id => $product) {
           echo $id . "  "; 
           echo padMbString($product['name'], $maxNameLength + 1);
           echo $product['price'];
           echo "\n";
        } 

        echo "   -----------\n";
        echo "0  ПОВЕРНУТИСЯ";
    } 
    
    function showReceipt($cart) {
        if(count($cart) == 0) {
            echo "КОШИК ПОРОЖНІЙ";
            return;
        }

        $maxNameLength = mb_strlen("НАЗВА");
        $maxPriceLength =  mb_strlen("ЦІНА");
        $maxCountLength =  mb_strlen("КІЛЬКІСТЬ");

        foreach ($cart as  $id => $product) {
            if(mb_strlen($product['name']) > $maxNameLength) {
                $maxNameLength = mb_strlen($product['name']); 
            }  

            if(mb_strlen($product['price']) > $maxPriceLength) {
                $maxPriceLength = mb_strlen($product['price']); 
            } 
            
            if(mb_strlen($product['quantity']) > $maxCountLength) {
               $maxCountLength = mb_strlen($product['quantity']); 
            } 
        } 

        echo "№  ";
        echo padMbString("НАЗВА", $maxNameLength + 2);
        echo padMbString("ЦІНА", $maxPriceLength + 2);
        echo padMbString("КІЛЬКІСТЬ", $maxCountLength + 2);
        echo("ВАРТІСТЬ \n");

        $totalSum = 0;
        foreach ($cart as  $id => $product) {
           echo $id . "  "; 
           echo padMbString($product['name'], $maxNameLength + 2);
           echo padMbString($product['price'], $maxPriceLength + 2);
           echo padMbString($product['quantity'], $maxCountLength + 2);
            
           $sum = (int)$product['quantity'] * (int)$product['price'];
           $totalSum += $sum;
           echo $sum;

           echo "\n";
        } 
        echo "РАЗОМ ДО CПЛАТИ: " .  $totalSum;
        echo "\n";
    }

     function setupProfile(&$profileName, &$profileAge) {
        echo "\nВаше ім'я: ";
        $name = getInput();
        
        if (!checkNameValidity($name)) {
            echo "ПОМИЛКА! Ім'я не може бути порожнім і повинно містити хоча б одну літеру.\n";
            return;
        }
        
        echo "Ваш вік: ";
        $age = getInput();
        
        if (!checkAgeValidity($age)) {
            echo "ПОМИЛКА! Вік повинен бути від 7 до 150 років.\n";
            return;
        }
        
        $profileName = $name;
        $profileAge = (int)$age;
        echo "Профіль налаштовано успішно! Привіт, $name!\n";
        updateProfileData($profileName, $profileAge);
    }

    function showCart($cart) {
         if(count($cart) == 0) {
            echo "КОШИК ПОРОЖНІЙ";
            return;
         }
         echo "У КОШИКУ:\n";

         $maxNameLength = mb_strlen("НАЗВА");
        
          foreach ($cart as  $id => $product) {
            if(mb_strlen($product['name']) > $maxNameLength) {
                $maxNameLength = mb_strlen($product['name']); 
            }  
           }

           echo padMbString("НАЗВА", $maxNameLength + 2);
           echo "КІЛЬКІСТЬ\n";

           foreach ($cart as  $id => $product) {
            echo padMbString($product['name'], $maxNameLength + 2);
            echo $product['quantity'];
            echo "\n";
        } 
        
    }

    function getProductData(&$products) {
       
        $productFile = 'products.csv';
        if (!file_exists($productFile)) {
            exit("Не вдалося знайти файл з продуктами.\n");
        }

        $handle = fopen($productFile, 'r');
        if (!$handle) {
            exit("Не вдалося відкрити файл з продуктами.\n");
        }

        while (($row = fgetcsv($handle, escape: "\\")) !== false) { 
            [$id, $name, $price] = $row;

            $products[$id] = [
            'name' => $name,
            'price' => $price
             ];

            
        }
        fclose($handle);
    }
       

    function getCartData(&$cart) {
         $cartFile = 'cart.csv';

        if (!file_exists($cartFile)) {
            exit("Не вдалося знайти файл з корзиною.\n");
        }

        $handle = fopen($cartFile, 'r');
        if (!$handle) {
            exit("Не вдалося відкрити файл з корзиною.\n");
        }

         while (($row = fgetcsv($handle, 0, ",", '"', "\\")) !== false) { 
            [$id, $name, $price, $quantity] = $row;
            $cart[$id] = [
            'name' => $name,
            'price' => $price,
            'quantity'=> $quantity
             ];
        }
        fclose($handle);
    }

    function updateCartData($cart) {
        $cartFile = 'cart.csv';
        
        if (!file_exists($cartFile)) {
            exit("Не вдалося знайти файл з корзиною.\n");
        }

        $handle = fopen($cartFile, 'w');
        if (!$handle) {
            exit("Не вдалося відкрити файл з корзиною.\n");
        }

        foreach ($cart as $id => $product) {
            fputcsv($handle, [$id, $product['name'], $product['price'], $product['quantity']], escape:"\\");
        }
        fclose($handle);
    }

    function getProfileData() {
        $userFile = 'profile.csv';

        if (!file_exists($userFile)) {
            exit("Не вдалося знайти файл з даними користувача.\n");
        }

        $handle = fopen($userFile, 'r');
        if (!$handle) {
            exit("Не вдалося відкрити файл з даними користувача.\n");
        } 
        fclose($handle);
    }

    function updateProfileData($profileName, $profileAge) {
        $userFile = 'profile.csv';

        if (!file_exists($userFile)) {
            exit("Не вдалося знайти файл з даними користувача.\n");
        }

        $handle = fopen($userFile, 'w');
        if (!$handle) {
            exit("Не вдалося відкрити файл з даними користувача.\n");
        } 

        fputcsv($handle, [$profileName, $profileAge], escape:"\\");

        fclose($handle);
    }

    function padMbString($input, $pad_length, $pad_string = ' ') {
        $inputLength = mb_strlen($input, 'UTF-8');
        $padAmount = $pad_length - $inputLength;
        if ($padAmount > 0) {
                return $input . str_repeat($pad_string, $padAmount);   
            }
        return $input;
    }   

    function checkNameValidity($name) : bool {
        if (empty($name)) {
            return false;
        }
        
        return preg_match('/[a-zA-Zа-яА-ЯіІїЇєЄґҐ]/u', $name);
    }
    
    function checkAgeValidity($age) : bool {
        if (!is_numeric($age)) {
            return false;
        }
        
        $age = (int)$age;
        return $age >= 7 && $age <= 150;
    }
    
    function getInput() : string {
        return readline();
    }


?>