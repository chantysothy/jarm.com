# Jarm.com - Micro CMS v.3  

[![Build Status](https://travis-ci.org/positronth/jarm.com.svg?branch=master)](https://travis-ci.org/positronth/jarm.com)
[![Build Status](https://scrutinizer-ci.com/g/positronth/jarm.com/badges/build.png?b=master)](https://scrutinizer-ci.com/g/positronth/jarm.com/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/positronth/jarm.com/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/positronth/jarm.com/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/positronth/jarm.com/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/positronth/jarm.com/?branch=master)  

.

## ตัวอย่าง
[jarmza.com](http://jarmza.com)

![Screenshot](https://github.com/positronth/jarm.com/raw/master/Screenshot.png)  

.

## เป้าหมาย  
เพื่อรองรับการใช้งานพร้อมกันจำนวนมาก โดยคำนึงถึงการใช้ทรัพยากรน้อยที่สุด แต่ยังคงความยืดหยุ่นในการใช้งานอยู่.  

.

## เกี่ยวกับซอสโค๊ดนี้  
โค๊ดชุดนี้เป็น OpenSource ซึ่งเป็นชุดเดียวกับที่ใช้งานจริงบนเว็บ [jarm.cm](https://jarm.com) แต่มีการลบค่า setting และรูปภาพบางส่วนออก เพื่อความปลอดภัย.  

.

## การทำงาน  
- jarm.com, \*.jarm.com - เรียกใช้งาน [php/start.php](https://github.com/positronth/jarm.com/blob/master/php/start.php)
- cdn.jarm.com/\* - เรียกใช้งาน files/cdn/\*
- f\*.jarm.com/\* - เรียกใช้งาน files/upload/\*
- upload รูปภาพ/ไฟล์ ในแต่ละเครื่องผ่าน port:81 - เรียกใช้งาน [php/upload.php](https://github.com/positronth/jarm.com/blob/master/php/upload.php)  

.

## การเรียกใช้งานภายใน App
 ตัวอย่าง:
 จากคอนฟิค(สำหรับซับโดเมน ent.jarm.com)
 > 'ent'=>['app'=>'news','arg'=>['cate'=>4,'hot'=>2],],

 มีการเรียกใช้หน้าเว็บด้วยลิ้งค์นี้
 > http://ent.jarm.com/view/11122  

  จะได้ว่า
 > [app-name] = news  
 > [first-path] = view  

  โดยเรียกใช้งาน App ชื่อว่า news และ Method ชื่อว่า \_view (ชื่อต้องขึ้นต้นด้วย _ เสมอ) และส่งตัวแปร arg
  ระบบจะค้นหาไฟล์เหล่านี้ โดยถ้าหากเจอไฟล์ใดก่อน ก็จะโหลดไฟล์นั้นมาใช้งานทันที (ระบบจะแปลงคำให้ขึ้นต้นด้วยตัวใหญ่เสมอ)
  - php/App/News/View.php
  - php/App/News/Service.php
  - php/App/News.php  

  เมื่อ require_once ไฟล์ดังกล่าวมาแล้ว จะทำการค้นหา method ต่อไปนี้
  - get_view (เฉพาะการเรียกใช้แบบ GET จาก \$\_SERVER['REQUEST_METHOD'])
  - post_view (เฉพาะการเรียกใช้แบบ POST จาก \$\_SERVER['REQUEST_METHOD'])
  - \_view (หากไม่เจอ method จากด้านบน)  

  หมายเหตุ
  - php/App/News/View.php เหมาะสำหรับ app ที่ method มีขนาดใหญ่ หรือโค๊ดจำนวนมาก
  - php/App/News/Service.php เหมาะสำหรับ app ขนาดกลาง ที่มีบาง method มีขนาดเล็กและใหญ่ ผสมกัน
  - php/App/News.php เหมาะสำหรับ app ขนาดเล็ก ที่มี method น้อยๆ หรือโค๊ดสั้นๆ


```php
->route([
  /**
  * ค่าเริ่มต้น:
  * - $key.app = $key (หากไม่กำหนด จะเรียกใช้ app ชื่อเดียวกับ $key [sub domain])
  * - $key.enable = true  (เปิดใช้งานโดยอัตโนมัติ ถ้ามีไฟล์ชื่อเดียวกับ app อยู่)
  * - $key.func = [first-path] (เรียกใช้ method พื้นฐาน ในกรณีที่ App ไม่มี method ที่ชื่อเดียวกับ _[first-path] )
  */
  'asiangames'=>['app'=>'news','arg'=>['cate'=>25],],
  'beauty'=>['app'=>'news','arg'=>['cate'=>27,'hot'=>31],],
  'chat'=>['enable'=>false],
  'eat'=>['app'=>'news','arg'=>['cate'=>32,'hot'=>31],],
  'ent'=>['app'=>'news','arg'=>['cate'=>4,'hot'=>2],],
  'game'=>['app'=>'news','arg'=>['cate'=>2,'hot'=>90],],
  'healthy'=>['app'=>'news','arg'=>['cate'=>34,'hot'=>31],],
  'home'=>['app'=>'news','arg'=>['cate'=>33,'hot'=>90],],
  'horo'=>['app'=>'news','arg'=>['cate'=>20,'hot'=>90],],
  'knowledge'=>['app'=>'news','arg'=>['cate'=>30,'hot'=>31],],
  'korea'=>['app'=>'news','arg'=>['cate'=>26,'hot'=>2],],
  'live'=>['app'=>'news','arg'=>['cate'=>35,'hot'=>3],],
  'lotto'=>['app'=>'news','arg'=>['cate'=>22],],
  'movie'=>['app'=>'news','arg'=>['cate'=>5,'hot'=>31],],
  'music'=>['app'=>'news','arg'=>['cate'=>24,'hot'=>31],],
  'pr'=>['app'=>'news','arg'=>['cate'=>28,'hot'=>31],],
  'tech'=>['app'=>'news','arg'=>['cate'=>3,'hot'=>31],],
  'weather'=>['app'=>'news','arg'=>['cate'=>21],],
])
```

.

## การเรียกใช้งานผ่าน Nginx  

**ตัวอย่างการเรียกใช้งานผ่าน Nginx + PHP-FPM บางส่วน**
```
server {
    listen  80;
    location / {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME /var/www/jarm.com/php/start.php;
        include fastcgi_params;
    }
}
```
```
server {
    listen  81;
    location / {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME /var/www/jarm.com/php/upload.php;
        include fastcgi_params;
    }
}
```
.

## ความต้องการของระบบ  
- [PHP](https://php.net) v. 7.1+ ขึ้นไป
- PECL/MongoDB (php-pecl-mongodb)
- PECL/Memcached (php-pecl-memcache)
- เซ็ทค่า date.timezone ในไฟล์ php.ini
- [Composer](https://getcomposer.org/) สำหรับอัพเดท libs ต่างๆ  


ในกรณีที่ใช้ docker สามารถดาวน์โหลด [Docker Image](https://hub.docker.com/r/positron/php/)  ได้ที่ repo  [positron/php](https://hub.docker.com/r/positron/php/)  

.

## เตือนความจำ  

ยิ่งเปิดเผย ยิ่งเห็นจุดบอด
