// Event.observe(window, 'load', init, false);

function init(){
 var url = 'https://hermes.ff-newmedia.net/apps/stats/curl-probe/ajax.php';
 var pars = 's=nodomain';
 var target = 'message';
 var myAjax = new Ajax.PeriodicalUpdater(target, url, 
   { method: 'get', 
     parameters: pars,
     frequency: 1,
     decay: 1});
}