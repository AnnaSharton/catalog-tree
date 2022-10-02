<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .container {display: flex; gap: 100px;}
        .upload {margin-top: 26px;}
        .upload, .catalog {padding: 10px 30px;}
        .form {display: flex; flex-direction: column; gap: 15px;}
        .file-added {font-size: 20px;border: 1px solid #000;background-color: lightgreen;padding: 10px;}
        p {margin-bottom:0}
        .error {border: 1px solid gray; background: #f59393; padding: 5px;}
        span::before {content:""; display: inline-block; margin-left:15px; background-position: center center;background-repeat: no-repeat; background-size: contain; background-image: url('https://e7.pngegg.com/pngimages/621/587/png-clipart-cross-illustration-no-symbol-computer-icons-red-cross-angle-text.png'); width:20px;height:20px; vertical-align: middle;}
    </style>
</head>
<body>
    <div class="container">
        <?php 
        error_reporting(E_ALL);
        mb_internal_encoding("UTF-8");
        $dir="uploads/";
        ?>



        <div class="upload">

            <?php
                //перед формой вывожу сообщения:
                if (isset($_GET['add']) and $_GET['add']==='uploaded') { //если все ок загружено и был редирект
                    echo '<br><div class="file-added">Файл добавлен</div>';
                }  
                else if (isset($_GET['add']) and $_GET['add']==='error1'){ //если загрузили файл но не того формата
                    echo '<br><div class="error">Файл неверного формата. Должен быть: .doc, .docx или .odt</div>';
                }
                else if (isset($_GET['add']) and $_GET['add']==='error2'){ //если нажали submit но забыли выбрать файл
                    echo '<br><div class="error">Файл не выбран</div>';
                }
            ?>

            <form class="form" action="" method="post" enctype="multipart/form-data">
                <p>Выберете файл формата doc, docx или odt</p>
                <input type="file" name="file">
                <input type="submit" name="submit" value="Загрузить">
            </form>
        </div>

        <?php  
            if($_FILES){
                $ext=pathinfo(($_FILES['file']['name']), PATHINFO_EXTENSION); //получаю расширение
                $structure = $dir.date("Y").'/'.date("m").'/'.date("d").'/'; //структура вложенных папок
                $newFileName=date("Y-m-d").'_'.date("H-i-s").'.'.$ext; //новое имя для файла
                //проверяю загружен ли файл, его расширение и нажато "отправить"	
                if ($_FILES and is_uploaded_file($_FILES['file']['tmp_name']) and isset($_POST['submit']) and ($ext==="doc" or $ext==="docx" or $ext==="odt")) { 						
                    //если все загружено и расширение ОК то нужно проверить существует ли уже директория и если да, то загрузить новый файл в нее, а если нет- то создать директорию
                    if (is_dir($structure)){                     
                    move_uploaded_file($_FILES['file']['tmp_name'], $structure.$newFileName);
                    header('Location: index.php?add=uploaded'); //редирект
                    } else { //если такой директрии нет, то сначала создаю ее
                        mkdir($structure, 0777, true);
                        move_uploaded_file($_FILES['file']['tmp_name'], $structure.$newFileName);
                        header('Location: index.php?add=uploaded'); 
                    }
                } 
                
                //проверяю если загружен но расширения не те
                else if ($_FILES and is_uploaded_file($_FILES['file']['tmp_name']) and isset($_POST['submit']) and ($ext!=="doc" or $ext!=="docx" or $ext!=="odt"))  {
                    header('Location: index.php?add=error1'); //если не тот формат файла, ошибка 1
                } //юзер не выбрал файл и нажал отправить:
                else if ($_FILES and !is_uploaded_file($_FILES['file']['tmp_name']) and isset($_POST['submit']))  {
                    header('Location: index.php?add=error2'); // если не выбран файл, ошибка 2
                }
            }

        ?>

        <div class="catalog">
            <?php //в правой части вывожу сообщение если был гет-запрос на удаление файла
                if (isset($_GET['delete'])) {
                    $fileDel = $_GET['delete']; 
                    unlink ($fileDel); // и если файл существует удаляю его
                    echo '<div class="file-added">Файл успешно удален</div>'; 
                } 
            ?>
            <h1>Каталог</h1>

            <?php 
                //функция для вывода списка директорий и файлов
                function dirList($dir){
                    $arrayFromDir = scandir($dir); //получаю массив из uploads
                    unset($arrayFromDir[array_search('.', $arrayFromDir, true)]);//удаляю элемент массива равный . или ..
                    unset($arrayFromDir[array_search('..', $arrayFromDir, true)]);
                    //прохожу по массиву и вывожу списком элемент массива
                    echo '<ul>';
                    foreach($arrayFromDir as $val){
                        if (is_file($dir.'/'.$val)) {echo '<li>'.$val.'<a class="link link-delete" href="index.php?delete='.$dir.'/'.$val. '"><span></span></a>';} //если файл является файлом то рядом отображаю картинку как ссылку для удаления
                        else if (is_dir($dir.'/'.$val)) { //и если полученный элемент массива является дерикторий
                                echo '<li>'.$val; //отображаю без картинки
                                dirList($dir.'/'.$val); //и повторяю цикл
                        echo '</li>';
                            } 
                    } echo '</ul>';
                }
                echo dirList($dir);
            ?>

        </div>
    </div>
</body>
</html>