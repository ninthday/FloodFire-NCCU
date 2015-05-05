php
===
- UnshortrenURL：短網址還原處理，搭配 ParseContent 取出短網址與還原
- ParseContent：資料內容解析

nodejs
===
提供一個 nodejs 版本且通用的短網址範本，還原短網址程式碼參考[1]

- unshorten.js [1]原始碼解讀版
- demo.js 使用範例

```
$ node demo.js http://goo.gl/clZfgB
The Original URL is: https://docs.google.com/spreadsheet/ccc?key=0AqWCmL1W-KNldHlOcjVJd2pkdENhd1lyNjdEOVFYeEE&usp=sharing#gid=0
$ node demo.js https://goo.gl/clZfgB
The Original URL is: https://docs.google.com/spreadsheet/ccc?key=0AqWCmL1W-KNldHlOcjVJd2pkdENhd1lyNjdEOVFYeEE&usp=sharing#gid=0
$ node demo.js https://goo.gl/clZfg
The Original URL is: http://podorozhniki.com/ru/driver/route/details.html?id=13761
$ node demo.js https://goo.gl/clZf
The Original URL is: http://ru.shvoong.com/portfolio/MyProfile.aspx?at=1&mode=translation
$ node demo.js https://goo.gl/clZ
The Original URL is: https://goo.gl/clZ
```

* 目前確定沒問題的短網址服務前綴 url:
  * http://goo.gl/
  * http://tinyurl.com/
  * http://git.io/
  * http://y2u.be/
  * http://youtu.be/
  * http://0rz.tw/
  * http://ptt.cc/

[1] https://github.com/mathiasbynens/node-unshorten