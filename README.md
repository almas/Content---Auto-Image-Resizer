# Танилцуулга ба Зааварчилгаа
Энэхүү Joomla АУС-ийн нэмэлт нь бичлэг дунд өөрийн сервер дээр /image хавтсанд хуулж оруулсан зургийн файлын өргөнийг шалган кодоор оруулахдаа width хувьсагчаар утга зааж жижигрүүлсэн эсэхийг шалгаж хэрэв том зургийг жижигрүүлж харагдуулж байгаа бол файлыг жижигрүүлэн хэрэглэгч рүү илгээнэ. Ингэснээр хэрэггүй том зургийг том хэвээр нь илгээлгүйгээр веб урсгал болон нээгдэх хурдыг нэмэгдүүлэх зорилготой.

### Хэрхэн суулгах вэ?
1. zip файлыг татаж авч сайтынхаа удирдлага руу нэвтэрч ороод Нэмэлт суулгагчаар суулгана
2. Нэмэлтийн удирдлага хэсэг рүү орж идэвхжүүлэхэд ажиллаж эхлэх 

### Хэрэглэх үед анхаарах зүйлс
png файлыг jpg болгож илүү bandwidth хэмнэж байгаа тул нэвт харагддаг png оруулах бол **class="transparent"** классыг зургийнхаа `<img` таг дотор бичиж өгөх хэрэгтэй. Жишээлбэл: `<img src="/image/stories/almas.png" class="transparent" style="width: 50px;" />` г.м.


##English
Dynamically resizes image in articles. 

This plugin is save server bandwidth and speedup page loading.

Plugin require GD, GD2 or php exec function and resize tool. 

You can use transparent png files with 'class="transparent"' class or it will be converted to no transparent jpg image.
