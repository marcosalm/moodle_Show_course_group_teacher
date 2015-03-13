<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block "people"
 *
 * @package     block
 * @subpackage  block_people
 * @copyright   2014
 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_people extends block_base {
    function init() {
	global $USER, $PAGE;
					$id = $PAGE->course->id;
					$name = $PAGE->course->fullname;
					$group = groups_get_user_groups($id, $USER->id);
					
					foreach($group as $g){
					$loop = count($g);
					for ($i = 0; $i < $loop; $i++){
					$groupname .= groups_get_group_name($g[$i]).' ';
					}
					}
					//print_r($groupname);
					//die;
					//$groupname = groups_get_group_name($group[0][0]);
					
        $this->title = '<span style="margin-top:-5px;position:absolute;">'.$name.' <br> <small>'.$groupname.'</small></span>';
    }

    function applicable_formats() {
        return array('course-view' => true, 'site' => true);
    }

    function has_config() {
        return false;
    }

    function instance_allow_multiple() {
        return false;
    }

    function instance_can_be_hidden() {
        return true;
    }
	
    function get_content() {
        global $COURSE, $CFG, $DB, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        // Prepare output
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        // Get context
        $currentcontext = $this->page->context;

        // Prepare multilang filter
        require_once(dirname(dirname(dirname(__FILE__))).'/filter/multilang/filter.php');
        $filter = new filter_multilang($currentcontext, array());

        // Get teachers ordered by roles
        $CFG->coursecontact = trim($CFG->coursecontact);
        if (!empty($CFG->coursecontact)) {
            $teacherroles = explode(',', $CFG->coursecontact);
            $teachers = get_role_users($teacherroles, $currentcontext, true, 'u.id, u.lastname, u.firstname, u.picture, u.imagealt, u.email, u.lastaccess, r.id AS role, r.sortorder', 'r.sortorder, u.lastname, u.firstname ASC');
        }

        // Get global role names and get role name aliases which have been renamed in course context (TODO: Alias handling could be done easier with core role_fix_names() function)
        $sql = 'SELECT r.id, r.name AS name, rn.name AS alias
                FROM {role} r
                LEFT OUTER JOIN {role_names} rn ON r.id = rn.roleid AND rn.contextid = ?';
        $rolenames = $DB->get_records_sql($sql, array($currentcontext->id));
		
	$timetoshowusers = 300; //Seconds default
  	$now = time();
	$timefrom = 100 * floor(($now - $timetoshowusers) / 100); // Round to nearest 100 seconds for better query cache
	
	

        // Output teachers
        if (!empty($teachers)) {
				$this->content->text .= html_writer::start_tag('div', array('class' => 'teachers'));

				$currentrole = 0;
					global $USER, $PAGE;
					$id = $PAGE->course->id;
					$group = groups_get_user_groups($id, $USER->id);

			
					
			if (!$group){
			
					foreach ($teachers as $t) {
					// Write heading and open new list if we get a new bunch of role members
					/*
					if ($currentrole != $t->role) {
						if ($currentrole != 0) {
							$this->content->text .= html_writer::end_tag('ul');
						}
						/*if ($rolenames[$t->role]->alias) {
							$this->content->text .= html_writer::tag('h3', 'Professor');
						}
						else {
							$this->content->text .= html_writer::tag('h3', 'Professor');
						}
						$this->content->text .= html_writer::start_tag('ul');
						$currentrole = $t->role;
					}*/

					// Output teacher
					$this->content->text .= html_writer::start_tag('li');
							//$group=groups_get_user_groups($id, $USER->id);
							//if (groups_is_member($group->id,$t->id));
							//
						// create user object for picture output
						$user = new stdClass();
						$user->id = $t->id;
						$user->lastname = $t->lastname;
						$user->firstname = $t->firstname;
						$user->picture = $t->picture;
						$user->imagealt = $t->imagealt;
						$user->email = $t->email;
						// foto professor
						
						$prof->id = $t->id;
						$profpic = new moodle_user_picture();
						$profpic->user = $prof;
						$profpic->courseid = $COURSE->id;
						$profpic->link = array('class'=>'banks','link'=>true);
						$this->content->text .= html_writer::start_tag('div', array('class' => 'mascara'));
						$this->content->text .= $OUTPUT->user_picture($profpic);
						$this->content->text .= html_writer::end_tag('div');
						$this->content->text .= html_writer::tag('strong', 'Professor');
						$this->content->text .= html_writer::start_tag('div', array('class' => 'name'));
						$this->content->text .= fullname($t);
						$this->content->text .= html_writer::end_tag('div');
						$this->content->text .= html_writer::start_tag('div', array('class' => 'icons'));
							if (has_capability('moodle/user:viewdetails', $currentcontext)) {
								$this->content->text .= html_writer::start_tag('a', array('href' => new moodle_url('/user/view.php', array('id' => $t->id, 'course' => $COURSE->id)), 'title' => get_string('viewprofile', 'core')));
								$this->content->text .= html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/user'), 'class' => 'icon', 'alt' => get_string('viewprofile', 'core')));
								$this->content->text .= html_writer::end_tag('a');
							}

							if ($CFG->messaging && has_capability('moodle/site:sendmessage', $currentcontext)) {
								$this->content->text .= html_writer::start_tag('a', array('href' => new moodle_url('/message/index.php', array('id' => $t->id)), 'title' => get_string('sendmessageto', 'core_message', fullname($t))));
									$this->content->text .= html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/email'), 'class' => 'icon', 'alt' => get_string('sendmessageto', 'core_message', fullname($t))));
								$this->content->text .= html_writer::end_tag('a');
							}
						$this->content->text .= html_writer::end_tag('div');
					$this->content->text .= html_writer::end_tag('li');
				}

				$this->content->text .= html_writer::end_tag('ul');
				//$this->content->text .= html_writer::end_tag('div');
			}
			else{
			foreach ($teachers as $t) {
										
							if (groups_is_member($group[0][0],$t->id)){
						
					// Write heading and open new list if we get a new bunch of role members
										
										if ($currentrole != $t->role) {
											if ($currentrole != 0) {
												$this->content->text .= html_writer::end_tag('ul');
											}
											if ($rolenames[$t->role]->alias) {
												$this->content->text .= html_writer::tag('h3', 'Professor');
											}
											else {
												$this->content->text .= html_writer::tag('h3', 'Professor');
											}
											$this->content->text .= html_writer::start_tag('ul');
											$currentrole = $t->role;
										}

										// Output teacher
										$this->content->text .= html_writer::start_tag('li');
										
											// create user object for picture output
											$t_user = new stdClass();
											$t_user->id = $t->id;
											$t_user->lastname = $t->lastname;
											$t_user->firstname = $t->firstname;
											$t_user->picture = $t->picture;
											$t_user->imagealt = $t->imagealt;
											$t_user->email = $t->email;
											$this->content->text .= html_writer::start_tag('a',  array('class'=>'foto_tutor', 'href' => new moodle_url('/user/view.php', array('id' => $t->id, 'course' => $COURSE->id)), 'title' => get_string('viewprofile', 'core')));
										 $lastaccess = $t->lastaccess;
										
										 if ( $lastaccess <= $now && $lastaccess > $timefrom) {
											$this->content->text .= html_writer::tag('p',"", array('class'=> 'on'));
										} else {
											$this->content->text .= html_writer::tag('p',"", array('class'=> 'off'));
										}
											
											$this->content->text .= $OUTPUT->user_picture($t_user, array('size' => '80', 'link' => false, 'courseid' => $COURSE->id));
											
											$this->content->text .= html_writer::end_tag('a');
											$this->content->text .= html_writer::start_tag('div', array('class' => 'name'));
											$this->content->text .= fullname($t);
											$this->content->text .= html_writer::end_tag('div');
											$this->content->text .= html_writer::start_tag('div', array('class' => 'icons'));
											 if ( $lastaccess <= $now && $lastaccess > $timefrom) {
											$this->content->text .= html_writer::tag('h3',"Online", array('class'=> 'on-teacher'));
										} else {
											$this->content->text .= html_writer::tag('h3',"Offline", array('class'=> 'off-teacher'));
										}
												if (has_capability('moodle/user:viewdetails', $currentcontext)) {
													$this->content->text .= html_writer::start_tag('a', array('class'=>'bt_perfil', 'href' => new moodle_url('/user/view.php', array('id' => $t->id, 'course' => $COURSE->id)), 'title' => get_string('viewprofile', 'core')));
													$this->content->text .= html_writer::tag('p', 'Ver Perfil');						
													$this->content->text .= html_writer::end_tag('a');
												}

												if ($CFG->messaging && has_capability('moodle/site:sendmessage', $currentcontext)) {
													if($t_user->id == $USER->id){
													$this->content->text .= html_writer::start_tag('a', array('class'=>'bt_mensagem','href' => new moodle_url('/message/index.php')));
													} else{
													$this->content->text .= html_writer::start_tag('a', array('class'=>'bt_mensagem','href' => new moodle_url('/message/index.php', array('id' => $t->id)), 'title' => get_string('sendmessageto', 'core_message', fullname($t))));
													}
													$this->content->text .= html_writer::tag('p', 'Mensagem');	
													$this->content->text .= html_writer::end_tag('a');
												}
											$this->content->text .= html_writer::end_tag('div');
										$this->content->text .= html_writer::end_tag('li');
							}
				

						$this->content->text .= html_writer::end_tag('ul');
					//	$this->content->text .= html_writer::end_tag('div');
					}
				}
		}
/*
        // Output participant list
        $this->content->text .= html_writer::start_tag('div', array('class' => 'participants'));
        $this->content->text .= html_writer::tag('h3', get_string('participants'));

        // Only if user is allow to see participants list
        if (has_capability('moodle/course:viewparticipants', $currentcontext)) {
            $this->content->text .= html_writer::start_tag('a', array('href' => new moodle_url('/user/index.php', array('contextid' => $currentcontext->id)), 'title' => get_string('participants')));
            $this->content->text .= html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/users'), 'class' => 'icon', 'alt' => get_string('participants')));
            $this->content->text .= "Mostrar Turma";
            $this->content->text .= html_writer::end_tag('a');
        }
        else {
            $this->content->text .= html_writer::start_tag('span', array('class' => 'hint'));
            $this->content->text .= get_string('noparticipantslist', 'block_people');
            $this->content->text .= html_writer::end_tag('span');
        }
*/
        //$this->content->text .= html_writer::end_tag('div');

        return $this->content;
    }
}
