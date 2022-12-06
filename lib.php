<?php
defined('MOODLE_INTERNAL') || die();

function assignfeedback_aiwriter_pluginfile($course,                                                                                    
                                        $cm,                                                                                        
                                        context $context,                                                                           
                                        $filearea,                                                                                  
                                        $args,                                                                                      
                                        $forcedownload) {                                                                           
    global $USER, $DB;                                                                                                              
                                                                                                                                    
    if ($context->contextlevel != CONTEXT_MODULE) {                                                                                 
        return false;                                                                                                               
    }                                                                                                                               
                                                                                                                                    
    require_login($course, false, $cm);                                                                                             
    $itemid = (int)array_shift($args);                                                                                              
    $record = $DB->get_record('assign_grades', array('id'=>$itemid), 'userid,assignment', MUST_EXIST);                              
    $userid = $record->userid;                                                                                                      
                                                                                                                                    
    if (!$assign = $DB->get_record('assign', array('id'=>$cm->instance))) {                                                         
        return false;                                                                                                               
    }                                                                                                                               
                                                                                                                                    
    if ($assign->id != $record->assignment) {                                                                                       
        return false;                                                                                                               
    }                                                                                                                               
                                                                                                                                    
    // Check is users feedback or has grading permission.                                                                           
    if ($USER->id != $userid and !has_capability('mod/assign:grade', $context)) {                                                   
        return false;                                                                                                               
    }                                                                                                                               
                                                                                                                                    
    $relativepath = implode('/', $args);                                                                                            
                                                                                                                                    
    $fullpath = "/{$context->id}/assignfeedback_aiwriter/$filearea/$itemid/$relativepath";                                              
                                                                                                                                    
    $fs = get_file_storage();                                                                                                       
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {                                                 
        return false;                                                                                                               
    }                                                                                                                               
    // Download MUST be forced - security!                                                                                          
    send_stored_file($file, 0, 0, true);                                                                                            
}    