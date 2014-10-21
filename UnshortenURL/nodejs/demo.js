
var unshorten = require('./unshorten.js');
if(process.argv.length != 3)
   {
      console.log('Usage: ndoe demo.js [http | https]://url');
      process.exit();
   }
   unshorten(process.argv[2], function(url){
      try{
         console.log('The Original URL is: ' + url);
      }catch(e){}
      
      process.exit();
   });
