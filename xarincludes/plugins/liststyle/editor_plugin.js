tinyMCE.importPluginLanguagePack('liststyle','en,sv');var TinyMCE_ListStylePlugin={getInfo:function(){return{longname:'List style',author:'Scott Eade - PolicyPoint Technologies Pty. Ltd.',authorurl:'http://policypoint.net',infourl:'http://policypoint.net/tinymce/docs/plugin_liststyle.html',version:'1.1.1'};},initInstance:function(inst){},getControlHTML:function(cn){switch(cn){case'liststyle':return tinyMCE.getButtonHTML(cn,'lang_liststyle_desc','{$pluginurl}/images/liststyle.gif','mceListStyle',true);}return'';},execCommand:function(editor_id,element,command,user_interface,value){switch(command){case'mceListStyle':var template=new Array();template['file']='../../plugins/liststyle/liststyle.htm';template['width']=300;template['height']=230;var listStyleType='',list='';var inst=tinyMCE.getInstanceById(editor_id);var selectedElement=inst.getFocusElement();while(selectedElement!=null&&selectedElement.nodeName!='LI')selectedElement=selectedElement.parentNode;if(selectedElement!=null){var listElement=tinyMCE.getParentElement(selectedElement,'ol,ul');if(listElement!=null){list=listElement.nodeName.toLowerCase();listStyleType=listElement.style.listStyleType?listElement.style.listStyleType:list=='ol'?'decimal':'disc';}tinyMCE.openWindow(template,{editor_id:editor_id,listStyleType:listStyleType,list:list,mceDo:'update'});}return true;}return false;},handleNodeChange:function(editor_id,node,undo_index,undo_levels,visual_aid,any_selection){tinyMCE.switchClass(editor_id+'_liststyle','mceButtonNormal');if(node==null)return;do{if(node.nodeName=='LI'){tinyMCE.switchClass(editor_id+'_liststyle','mceButtonSelected');return;}}while((node=node.parentNode));}};tinyMCE.addPlugin('liststyle',TinyMCE_ListStylePlugin);