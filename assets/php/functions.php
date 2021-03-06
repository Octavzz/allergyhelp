<?php
	include "config.php";
	date_default_timezone_set('Europe/Bucharest');

	if(isset($_FILES['allergy-content-img']))
	{
		$name = md5(rand(100, 200));
		$ext = pathinfo($_FILES["allergy-content-img"]["name"], PATHINFO_EXTENSION);
		$filename = $name . '.' . $ext;
		$destination = '../img/uploads/' . $filename;
		$location = $_FILES["allergy-content-img"]["tmp_name"];
		$allowed = array('png', 'jpg', 'gif', 'bmp');
		if (in_array(strtolower($ext), $allowed))
		{
			move_uploaded_file($location, $destination);
			echo '../assets/img/uploads/' . $filename;
		}
	}
	class Utils
	{
		public $db;
		public function __construct()
		{
			$this->db = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
			if(mysqli_connect_errno())
			{
				echo "Nu s-a putut face conexiunea la baza de date!";
				exit;
			}
			mysqli_set_charset($this->db, 'utf8mb4');
		}
		public function get_ip_address()
		{
			if (!empty($_SERVER['HTTP_CLIENT_IP']))
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else
				$ip = $_SERVER['REMOTE_ADDR'];
			return $ip;
		}
		public function mysqli_result($res, $row = 0, $col = 0)
		{
			$numrows = mysqli_num_rows($res);
			if ($numrows && $row <= ($numrows - 1) && $row >= 0)
			{
				mysqli_data_seek($res,$row);
				$resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
				if (isset($resrow[$col]))
					return $resrow[$col];
			}
			return false;
		}
		public function time_passed($timestamp)
		{
			$timestamp      = strtotime($timestamp);
			$current_time   = strtotime(date('Y-m-d H:i:s', time()));
			$diff           = $current_time - $timestamp;

			$intervals      = array (
				'year' => 31556926, 'month' => 2629744, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'minute'=> 60
			);

			$prepoz = "de";

			if ($diff == 0)
			{
				return 'chiar acum';
			}

			if ($diff < 60)
			{
				if($diff < 20) $prepoz = "";
				return $diff == 1 ? 'acum o secundă' : 'acum ' . $diff . ' '. $prepoz .' secunde';
			}

			if ($diff >= 60 && $diff < $intervals['hour'])
			{
				$diff = floor($diff/$intervals['minute']);
				if($diff < 20) $prepoz = "";
				return $diff == 1 ? 'acum un minut' : 'acum ' . $diff . ' '. $prepoz .' minute';
			}

			if ($diff >= $intervals['hour'] && $diff < $intervals['day'])
			{
				$diff = floor($diff/$intervals['hour']);
				if($diff < 20) $prepoz = "";
				return $diff == 1 ? 'acum o oră' : 'acum ' . $diff . ' '. $prepoz .' ore';
			}

			if ($diff >= $intervals['day'] && $diff < $intervals['week'])
			{
				$diff = floor($diff/$intervals['day']);
				if($diff < 20) $prepoz = "";
				return $diff == 1 ? 'acum o zi' : 'acum ' . $diff . ' '. $prepoz .' zile';
			}

			if ($diff >= $intervals['week'] && $diff < $intervals['month'])
			{
				$diff = floor($diff/$intervals['week']);
				if($diff < 20) $prepoz = "";
				return $diff == 1 ? 'acum o săptămână' : 'acum ' . $diff . ' '. $prepoz .' săptămâni';
			}

			if ($diff >= $intervals['month'] && $diff < $intervals['year'])
			{
				$diff = floor($diff/$intervals['month']);
				if($diff < 20) $prepoz = "";
				return $diff == 1 ? 'acum o lună' : 'acum ' . $diff . ' '. $prepoz .' luni';
			}

			if ($diff >= $intervals['year'])
			{
				$diff = floor($diff/$intervals['year']);
				if($diff < 20) $prepoz = "";
				return $diff == 1 ? 'acum un an' : 'acum ' . $diff . ' '. $prepoz .' ani';
			}
		}
	}
	class User extends Utils
	{
		public function check_login($email, $password)
		{
			$email = mysqli_real_escape_string($this->db, $email);
			$password = sha1($password);
			$sql="SELECT id FROM users WHERE email='$email' AND password='$password'";

			$result = mysqli_query($this->db,$sql);
			$user_data = mysqli_fetch_array($result);
			$count_row = $result->num_rows;

			if($count_row == 1)
			{
				$_SESSION['allergyhelp_login'] = true;
				$_SESSION['allergyhelp_id'] = $user_data['id'];
				return 1;
			}
			return 0;
		}
		public function register($email, $pass, $lastname, $fistname)
		{
			$email = mysqli_real_escape_string($this->db, $email);
			$lastname = mysqli_real_escape_string($this->db, $lastname);
			$firstname = mysqli_real_escape_string($this->db, $fistname);

			$pass = sha1($pass);
			$sql = "SELECT * FROM users WHERE email='$email'";

			$check =  $this->db->query($sql);
			$count_row = $check->num_rows;

			if ($count_row == 0)
			{
				$sql = "INSERT INTO users SET email='$email', password='$pass', lastname='$lastname', firstname='$firstname', regtime='".date('Y-m-d H:i:s', time())."'";
				mysqli_query($this->db,$sql);

				$uid = mysqli_insert_id($this->db);
				$this->add_notification($uid, "Bine ai venit pe AllergyHelp!", "Ți-ai creat contul cu succes. Acum ai acces la tot conținutul site-ului. Pentru a primi notificări cu privire la anumite alergii, îți recomandăm să îți alegi simptomele și cauzele.", "?p=profile");
				$this->bot_reply_register($uid);
				return 1;
			}
			return 0;
		}
		public function edit_profile($id, $email, $pass = "", $lastname, $fistname)
		{
			if(!empty($pass))
			{
				$pass = sha1($pass);
				$sql = "UPDATE users SET password='$pass' WHERE id = '$id'";
				$result = mysqli_query($this->db, $sql);
				if($result) $_SESSION['allergyhelp_change_pass_success'] = true;
				else $_SESSION['allergyhelp_change_pass_fail'] = true;
			}
			$email = mysqli_real_escape_string($this->db, $email);
			$lastname = mysqli_real_escape_string($this->db, $lastname);
			$firstname = mysqli_real_escape_string($this->db, $fistname);

			$sql = "UPDATE users SET email='$email', lastname='$lastname', firstname='$firstname' WHERE id = '$id'";
			$result = mysqli_query($this->db,$sql);
			return $result;
		}
		public function get_fullname($id)
		{
			$sql = "SELECT firstname, lastname FROM users WHERE id = $id";
			$result = mysqli_query($this->db,$sql);
			$user_data = mysqli_fetch_array($result);
			return $user_data['firstname']." ".$user_data['lastname'];
		}
		public function get_firstname($id)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT firstname FROM users WHERE id = '$id'"));
		}
		public function get_lastname($id)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT lastname FROM users WHERE id = '$id'"));
		}
		public function get_email($id)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT email FROM users WHERE id = '$id'"));
		}
		public function get_allergy_cover($id)
		{
			if(file_exists("assets/img/allergies/".$id.".jpg")) $cover = $id;
			else $cover = 0;
			$source = "assets/img/allergies/" . $cover . ".jpg?=" . filemtime('assets/img/allergies/'.$cover.'.jpg');
			return $source;
		}
		public function get_avatar($id)
		{
			if(file_exists("assets/img/avatars/".$id.".jpg")) $avatar = $id;
			else $avatar = 0;
			$source = "assets/img/avatars/" . $avatar . ".jpg?=" . filemtime('assets/img/avatars/'.$avatar.'.jpg');
			return $source;
		}
		public function get_last_allergies_landing()
		{
			$sql = "SELECT * FROM allergies ORDER BY date DESC LIMIT 3";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
					<div class="col-md-4">
						<div class="card card-plain">
							<div class="card-header card-header-image">
								<img class="img" src="'.$this->get_allergy_cover($row['id']).'">
								<div class="colored-shadow" style="background-image: url('.$this->get_allergy_cover($row['id']).'); opacity: 1;"></div>
							</div>
							<h3 class="card-title">
								'.$row['name'].'
							</h3>
							<p class="card-description">
								'.mb_strimwidth(strip_tags($row['content']), 0, 300, "...").'
							</p>
						</div>
					</div>
					';
				}
			}
		}
		public function get_all_allergies()
		{
			$sql = "SELECT * FROM allergies ORDER BY date DESC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
					<a href="index.php?p=allergy&a='.$row['id'].'">
						<div class="card card-plain">
							<div class="row">
								<div class="col-md-4 col-lg-3">
									<div class="card-header card-header-image">
										<img class="img" src="'.$this->get_allergy_cover($row['id']).'">
										<div class="colored-shadow" style="background-image: url('.$this->get_allergy_cover($row['id']).'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-md-8 col-lg-9">
									<h4 class="card-title mb-0">
										'.$row['name'].'
									</h4>
									<p class="categories my-1">';
					$this->get_allergy_signs($row['id']);
					$this->get_allergy_causes($row['id']);
					echo '
									</p>
									<p class="card-description">
										'.mb_strimwidth(strip_tags($row['content']), 0, 300, "...").'
									</p>
									<p class="author">
										<img src="'.$this->get_avatar($row['author']).'" class="avatar" />
										<strong>'.$this->get_firstname($row['author']).' '.$this->get_lastname($row['author']).'</strong>
										<br />'.$this->time_passed($row['date']).'
									</p>
								</div>
							</div>
						</div>
					</a>
					';
				}
			}
		}
		public function get_last_allergies()
		{
			$frequent = $this->mysqli_result(mysqli_query($this->db, "SELECT COUNT(*) FROM allergies WHERE frequent = 1"));
			$sql = "SELECT * FROM allergies ORDER BY date DESC LIMIT $frequent";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
					<a href="index.php?p=allergy&a='.$row['id'].'">
						<div class="card card-plain">
							<div class="row">
								<div class="col-md-4 col-lg-3">
									<div class="card-header card-header-image">
										<img class="img" src="'.$this->get_allergy_cover($row['id']).'">
										<div class="colored-shadow" style="background-image: url('.$this->get_allergy_cover($row['id']).'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-md-8 col-lg-9">
									<h4 class="card-title mb-0">
										'.$row['name'].'
									</h4>
									<p class="categories my-1">';
					$this->get_allergy_signs($row['id']);
					$this->get_allergy_causes($row['id']);
					echo '
									</p>
									<p class="card-description">
										'.mb_strimwidth(strip_tags($row['content']), 0, 300, "...").'
									</p>
									<p class="author">
										<img src="'.$this->get_avatar($row['author']).'" class="avatar" />
										<strong>'.$this->get_firstname($row['author']).' '.$this->get_lastname($row['author']).'</strong>
										<br />'.$this->time_passed($row['date']).'
									</p>
								</div>
							</div>
						</div>
					</a>
					';
				}
			}
		}
		public function get_favorite_allergies($id)
		{
			$sql = "SELECT allergies.id, allergies.name, allergies.content, allergies.date, allergies.author FROM allergies INNER JOIN user_allergies ON allergies.id = user_allergies.allergy WHERE user_allergies.user = '$id' ORDER BY allergies.date DESC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
					<a href="index.php?p=allergy&a='.$row['id'].'">
						<div class="card card-plain">
							<div class="row">
								<div class="col-md-4 col-lg-3">
									<div class="card-header card-header-image">
										<img class="img" src="'.$this->get_allergy_cover($row['id']).'">
										<div class="colored-shadow" style="background-image: url('.$this->get_allergy_cover($row['id']).'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-md-8 col-lg-9">
									<h4 class="card-title mb-0">
										'.$row['name'].'
									</h4>
									<p class="categories my-1">';
					$this->get_allergy_signs($row['id']);
					$this->get_allergy_causes($row['id']);
					echo '
									</p>
									<p class="card-description">
										'.mb_strimwidth(strip_tags($row['content']), 0, 300, "...").'
									</p>
									<p class="author">
										<img src="'.$this->get_avatar($row['author']).'" class="avatar" />
										<strong>'.$this->get_firstname($row['author']).' '.$this->get_lastname($row['author']).'</strong>
										<br />'.$this->time_passed($row['date']).'
									</p>
								</div>
							</div>
						</div>
					</a>
					';
				}
			}
			else echo "Nu ai niciun articol adăugat la favorite!";
		}
		public function get_recommended_allergies($id)
		{
			$sql = "SELECT DISTINCT allergies.id, allergies.name, allergies.content, allergies.date, allergies.author FROM allergies INNER JOIN allergy_signs ON allergy_signs.allergy = allergies.id INNER JOIN user_signs ON user_signs.sign = allergy_signs.sign WHERE user_signs.user = '$id' UNION SELECT DISTINCT allergies.id, allergies.name, allergies.content, allergies.date, allergies.author FROM allergies INNER JOIN allergy_causes ON allergy_causes.allergy = allergies.id INNER JOIN user_causes ON user_causes.cause = allergy_causes.cause WHERE user_causes.user = '$id' ORDER BY date DESC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
					<a href="index.php?p=allergy&a='.$row['id'].'">
						<div class="card card-plain">
							<div class="row">
								<div class="col-md-4 col-lg-3">
									<div class="card-header card-header-image">
										<img class="img" src="'.$this->get_allergy_cover($row['id']).'">
										<div class="colored-shadow" style="background-image: url('.$this->get_allergy_cover($row['id']).'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-md-8 col-lg-9">
									<h4 class="card-title mb-0">
										'.$row['name'].'
									</h4>
									<p class="categories my-1">';
					$this->get_allergy_signs($row['id']);
					$this->get_allergy_causes($row['id']);
					echo '
									</p>
									<p class="card-description">
										'.mb_strimwidth(strip_tags($row['content']), 0, 300, "...").'
									</p>
									<p class="author">
										<img src="'.$this->get_avatar($row['author']).'" class="avatar" />
										<strong>'.$this->get_firstname($row['author']).' '.$this->get_lastname($row['author']).'</strong>
										<br />'.$this->time_passed($row['date']).'
									</p>
								</div>
							</div>
						</div>
					</a>
					';
				}
			}
			else echo "Nu este înregistrată nicio alergie pe baza simptomelor și cauzelor salvate în cont!";
		}
		public function get_frequent_allergies()
		{
			$sql = "SELECT * FROM allergies WHERE frequent = 1 ORDER BY date DESC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
					<a href="index.php?p=allergy&a='.$row['id'].'">
						<div class="card card-plain">
							<div class="row">
								<div class="col-md-4 col-lg-3">
									<div class="card-header card-header-image">
										<img class="img" src="'.$this->get_allergy_cover($row['id']).'">
										<div class="colored-shadow" style="background-image: url('.$this->get_allergy_cover($row['id']).'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-md-8 col-lg-9">
									<h4 class="card-title mb-0">
										'.$row['name'].'
									</h4>
									<p class="categories my-1">';
					$this->get_allergy_signs($row['id']);
					$this->get_allergy_causes($row['id']);
					echo '
									</p>
									<p class="card-description">
										'.mb_strimwidth(strip_tags($row['content']), 0, 300, "...").'
									</p>
									<p class="author">
										<img src="'.$this->get_avatar($row['author']).'" class="avatar" />
										<strong>'.$this->get_firstname($row['author']).' '.$this->get_lastname($row['author']).'</strong>
										<br />'.$this->time_passed($row['date']).'
									</p>
								</div>
							</div>
						</div>
					</a>
					';
				}
			}
		}
		public function get_allergy_signs($allergy)
		{
			$sql = "SELECT signs.sign FROM allergy_signs INNER JOIN signs ON allergy_signs.sign = signs.id WHERE allergy_signs.allergy = '$allergy' ORDER BY allergy_signs.sign ASC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
				while($row = mysqli_fetch_assoc($result))
					echo '<span class="text-danger mr-3">'.$row['sign'].'</span>';
		}
		public function get_allergy_causes($allergy)
		{
			$sql = "SELECT causes.cause FROM allergy_causes INNER JOIN causes ON allergy_causes.cause = causes.id WHERE allergy_causes.allergy = '$allergy' ORDER BY allergy_causes.cause ASC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
				while($row = mysqli_fetch_assoc($result))
					echo '<span class="text-warning mr-3">'.$row['cause'].'</span>';
		}
		public function get_signs_for_user($userid)
		{
			$sql = "SELECT * FROM signs ORDER BY sign ASC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
				while($row = mysqli_fetch_assoc($result))
				{
					if($this->is_sign_added_to_user($userid, $row['id'])) echo '<a href="?dels='.$row['id'].'" class="btn btn-dark m-2 text-white"><i class="fa fa-fw fa-minus"></i> '.$row['sign'].'</a>';
					else echo '<a href="?adds='.$row['id'].'" class="btn btn-danger m-2 text-white">'.$row['sign'].'</a>';
				}
		}
		public function get_causes_for_user($userid)
		{
			$sql = "SELECT * FROM causes ORDER BY cause ASC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
				while($row = mysqli_fetch_assoc($result))
				{
					if($this->is_cause_added_to_user($userid, $row['id'])) echo '<a href="?delc='.$row['id'].'" class="btn btn-dark m-2 text-white"><i class="fa fa-fw fa-minus"></i> '.$row['cause'].'</a>';
					else echo '<a href="?addc='.$row['id'].'" class="btn btn-warning m-2">'.$row['cause'].'</a>';
				}
		}
		public function allergy_exists($id)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT COUNT(*) FROM allergies WHERE id = '$id'"));
		}
		public function get_allergy_name($id)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT name FROM allergies WHERE id = '$id'"));
		}
		public function get_allergy_content($id)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT content FROM allergies WHERE id = '$id'"));
		}
		public function get_allergy_author($id)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT author FROM allergies WHERE id = '$id'"));
		}
		public function get_allergy_date($id)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT date FROM allergies WHERE id = '$id'"));
		}
		public function is_allergy_added_to_user($user, $allergy)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT * FROM user_allergies WHERE user='$user' AND allergy='$allergy'"));
		}
		public function is_sign_added_to_user($user, $sign)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT * FROM user_signs WHERE user='$user' AND sign='$sign'"));
		}
		public function is_cause_added_to_user($user, $cause)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT * FROM user_causes WHERE user='$user' AND cause='$cause'"));
		}
		public function add_allergy_to_user($user, $allergy)
		{
			$sql = "SELECT * FROM user_allergies WHERE user='$user' AND allergy='$allergy'";

			$check =  $this->db->query($sql);
			$count_row = $check->num_rows;

			if ($count_row == 0)
			{
				$sql = "INSERT INTO user_allergies SET user='$user', allergy='$allergy'";
				mysqli_query($this->db,$sql);
				return 1;
			}
			return 0;
		}
		public function add_sign_to_user($user, $sign)
		{
			$sql = "SELECT * FROM user_signs WHERE user='$user' AND sign='$sign'";

			$check =  $this->db->query($sql);
			$count_row = $check->num_rows;

			if ($count_row == 0)
			{
				$sql = "INSERT INTO user_signs SET user='$user', sign='$sign'";
				mysqli_query($this->db,$sql);
				return 1;
			}
			return 0;
		}
		public function add_cause_to_user($user, $cause)
		{
			$sql = "SELECT * FROM user_causes WHERE user='$user' AND cause='$cause'";

			$check =  $this->db->query($sql);
			$count_row = $check->num_rows;

			if ($count_row == 0)
			{
				$sql = "INSERT INTO user_causes SET user='$user', cause='$cause'";
				mysqli_query($this->db,$sql);
				return 1;
			}
			return 0;
		}
		public function delete_allergy_from_user($user, $allergy)
		{
			return mysqli_query($this->db, "DELETE FROM user_allergies WHERE user='$user' AND allergy='$allergy'");
		}
		public function delete_sign_from_user($user, $sign)
		{
			return mysqli_query($this->db, "DELETE FROM user_signs WHERE user='$user' AND sign='$sign'");
		}
		public function delete_cause_from_user($user, $cause)
		{
			return mysqli_query($this->db, "DELETE FROM user_causes WHERE user='$user' AND cause='$cause'");
		}
		public function get_notifications($id)
		{
			$sql = "SELECT * FROM notifications WHERE user = '$id' ORDER BY date DESC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				echo '<div class="notifications-list">';
				while($row = mysqli_fetch_assoc($result))
				{
					$unread = "";
					if(!$row['dismissed']) $unread = " unread";
					echo '
						<a href="'.$row['link'].'">
						<div class="notification'.$unread.'">
							<strong>'.$row['title'].'</strong>
							<br />'.$row['content'].'
							<br /><small class="text-muted"><i class="fa fa-fw fa-clock"></i> '.$this->time_passed($row['date']).'</small>
						</div>
					</a>
					';
				}
				echo '</div>';
			}
			else echo '<div class="no-notifications">Toate notificările tale vor apărea aici!</div>';
		}
		public function send_message($id, $subject, $message)
		{
			$subject = mysqli_real_escape_string($this->db, $subject);
			$message = mysqli_real_escape_string($this->db, $message);

			$sql = "INSERT INTO conversations SET subject = '$subject', userid = '$id', date = '".date('Y-m-d H:i:s', time())."'";
			$conv = mysqli_query($this->db, $sql);
			if(!$conv) return 0;

			$conv = mysqli_insert_id($this->db);
			$sql = "INSERT INTO messages SET message = '$message', userid = '$id', conversation = '$conv', date = '".date('Y-m-d H:i:s', time())."'";
			$mess = mysqli_query($this->db, $sql);
			if(!$mess) return 0;

			return 1;
		}
		public function send_reply($id, $message, $conversation)
		{
			$message = mysqli_real_escape_string($this->db, $message);

			if($id !== $this->mysqli_result(mysqli_query($this->db, "SELECT userid FROM conversations WHERE id = '$conversation'")))
				return 0;

			$sql = "INSERT INTO messages SET message = '$message', userid = '$id', conversation = '$conversation', date = '".date('Y-m-d H:i:s', time())."'";
			mysqli_query($this->db, $sql);
			$sql = "UPDATE conversations SET date = '".date('Y-m-d H:i:s', time())."' WHERE id = '$conversation'";
			mysqli_query($this->db, $sql);
			return 1;
		}
		public function get_conversations($id)
		{
			$sql = "SELECT * FROM conversations WHERE userid = '$id' ORDER BY unread DESC, date DESC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				echo '
					<h2 class="title mt-4 mb-1">Mesajele mele</h2>
					<ul class="list-group">
					';
				while($row = mysqli_fetch_assoc($result))
				{
					if(!$row['unread']) $readcls = " msgread";
					else $readcls = "";
					echo '
						<a class="list-group-item'.$readcls.'" href="?p=messages&m='.$row['id'].'">
							<strong>'.$row['subject'].'</strong>
							<br /><small class="text-muted"><i class="fa fa-fw fa-clock"></i> '.$this->time_passed($row['date']).'</small>
						</a>
						';
				}
				echo '</ul>';
			}
		}
		public function get_conversation($id, $conversation)
		{
			$sql = "SELECT subject FROM conversations WHERE userid = '$id' AND id = '$conversation'";
			$result = mysqli_query($this->db, $sql);
			if(!mysqli_num_rows($result))
			{
				echo '<h3 class="title m-0">Conversația nu există!</h3><a href="?p=messages">Înapoi la mesaje</a>';
				return 0;
			}
			$conv = mysqli_fetch_assoc($result);
			echo '<h2 class="title">'.$conv['subject'].'</h2>';
			$sql = "SELECT * FROM messages WHERE conversation = '$conversation' ORDER BY date ASC, id ASC";
			$result = mysqli_query($this->db, $sql);
			echo '
				<div class="msg-box">
					<div class="msg-list">
				';
			while($row = mysqli_fetch_assoc($result))
			{
				if($row['userid'] === $id) $reply_user = " reply-user";
				else $reply_user = "";
				echo '<div class="reply'.$reply_user.'">';
				if($row['userid'] !== $id)
					echo '<div class="reply-name">'.$this->get_fullname($row["userid"]).'</div>';
				echo '<img class="avatar reply-avatar" src="'.$this->get_avatar($row['userid']).'" />';
				echo '<div class="reply-text">'.$row['message'].'</div></div>';
			}
			echo '
					</div>
					<form action="" method="post" name="new_reply">
						<input class="reply-box" type="text" name="reply" placeholder="Răspunde..." required autofocus></input>
						<button type="submit" class="send-reply" name="new_reply"><i class="fa fa-paper-plane"></i></button>
					</form>
				</div>
				';
			$sql = "UPDATE conversations SET unread = 0 WHERE id = '$conversation'";
			mysqli_query($this->db, $sql);
		}
		public function get_bot_conversation($id)
		{
			$sql = "SELECT * FROM bot_messages WHERE userid = '$id' ORDER BY date ASC, id ASC";
			$result = mysqli_query($this->db, $sql);
			echo '
				<div class="msg-box">
					<div class="msg-list">
				';
			while($row = mysqli_fetch_assoc($result))
			{
				if(!$row['frombot']) $reply_user = " reply-user";
				else $reply_user = "";
				echo '<div class="reply'.$reply_user.'">';
				if($row['frombot']) echo '<img class="avatar reply-avatar" src="'.$this->get_avatar(-1).'" />';
				else echo '<img class="avatar reply-avatar" src="'.$this->get_avatar($row['userid']).'" />';
				echo '<div class="reply-text">'.$row['message'].'</div></div>';
			}
			echo '
					</div>
					<form action="" method="post" name="bot_reply">
						<input class="reply-box" type="text" name="reply" placeholder="Răspunde..." required autofocus></input>
						<button type="submit" class="send-reply" name="bot_reply"><i class="fa fa-paper-plane"></i></button>
					</form>
				</div>
				';
		}
		public function bot_reply_register($id)
		{
			$message = "Bună, ".$this->get_firstname($id)."!<br />Eu sunt AllergyBot, dar poți să-mi spui și Botzică! :)<br />Scrie <strong>ajutor</strong> pentru a afla ce pot să fac!";
			mysqli_query($this->db, "INSERT INTO bot_messages SET message = '$message', frombot = 1, userid = '$id', date = '".date('Y-m-d H:i:s', time())."'");
		}
		public function send_bot_reply($id, $reply)
		{
			$replt = mysqli_real_escape_string($this->db, $reply);

			$sql = "INSERT INTO bot_messages SET message = '$reply', frombot = 0, userid = '$id', date = '".date('Y-m-d H:i:s', time())."'";
			mysqli_query($this->db, $sql);

			$cmd = explode(' ', trim($reply), 2);
			$cmd[0] = strtolower($cmd[0]);
			$cmd[1] = strtolower($cmd[1]);

			if($cmd[0] == "comenzi" || $cmd[0] == "ajutor" || $cmd[0] == "help")
			{
				$botmsg = "<i>info</i> <strong>&lt;nume alergie&gt;</strong>
				<br />Îți ofer linkul către o alergie dată de tine.
				<br /><br /><i>simptome</i> <strong>&lt;nume alergie&gt;</strong>
				<br />Îți enumăr simptomele pentru o anumită alergie.
				<br /><br /><i>cauze</i> <strong>&lt;nume alergie&gt;</strong>
				<br />Îți enumăr cauzele pentru o anumită alergie.
				<br /><br /><i>simptom</i> <strong>&lt;nume simptom&gt;</strong>
				<br />Îți enumăr alergiile ce au un anumit simptom.
				<br /><br /><i>cauza</i> <strong>&lt;nume cauză&gt;</strong>
				<br />Îți enumăr alergiile ce au o anumită cauză.
				<br /><br /><i>ultima</i>
				<br />Îți ofer linkul către ultima alergie publicată.
				<br /><br /><i>oarecare</i>
				<br />Îți ofer linkul către o alergie oarecare.
				<br /><br /><i>contact</i>
				<br />Te ajut să contactezi un administrator.
				<br /><br /><i>sterge</i>
				<br />Șterg mesajele din această conversație.";
			}
			else if($cmd[0] == "info" || $cmd[0] == "detalii" || $cmd[0] == "informații" || $cmd[0] == "informatii" || $cmd[0] == "alergie" || $cmd[0] == "alergia")
			{
				if(empty($cmd[1])) $botmsg = "Mod de folosire:<br /><i>".$cmd[0]."</i> <strong>alergie</strong>";
				else
				{
					$sql = "SELECT id, name FROM allergies WHERE name LIKE '%$cmd[1]%'";
					$result = mysqli_query($this->db, $sql);
					if(mysqli_num_rows($result))
					{
						if(mysqli_num_rows($result) === 1) $botmsg = "Am găsit următoarea alergie:";
						else $botmsg = "Am găsit următoarele <strong>".mysqli_num_rows($result)."</strong> alergii:";
						while($row = mysqli_fetch_assoc($result))
							$botmsg .= "<br /><a href=?p=allergy&a=".$row['id']."><strong>".$row['name']."</strong></a>";
					}
					else $botmsg = "Nu am găsit nicio alergie numită astfel.<br />Încearcă din nou!";
				}
			}
			else if($cmd[0] == "simptome")
			{
				if(empty($cmd[1])) $botmsg = "Mod de folosire:<br /><i>".$cmd[0]."</i> <strong>alergie</strong>";
				else
				{
					$aid = $this->mysqli_result(mysqli_query($this->db, "SELECT id FROM allergies WHERE name LIKE '%$cmd[1]%' ORDER BY date DESC LIMIT 1"));
					$aname = $this->mysqli_result(mysqli_query($this->db, "SELECT name FROM allergies WHERE id = '$aid'"));
					if($aid)
					{
						$sql = "SELECT signs.sign FROM allergy_signs INNER JOIN signs ON allergy_signs.sign = signs.id WHERE allergy_signs.allergy = '$aid' ORDER BY allergy_signs.sign ASC";
						$result = mysqli_query($this->db, $sql);
						if(mysqli_num_rows($result))
						{
							$botmsg = "Simptome - <strong>".$aname."</strong>:";
							while($row = mysqli_fetch_assoc($result))
								$botmsg .= "<br />".$row['sign'];
						}
						else $botmsg = "Nu s-au găsit simptome pentru această alergie.";
					}
					else $botmsg = "Nu am găsit nicio alergie numită astfel.<br />Încearcă din nou!";
				}
			}
			else if($cmd[0] == "simptom")
			{
				if(empty($cmd[1])) $botmsg = "Mod de folosire:<br /><i>".$cmd[0]."</i> <strong>nume simptom</strong>";
				else
				{
					$sid = $this->mysqli_result(mysqli_query($this->db, "SELECT id FROM signs WHERE sign LIKE '%$cmd[1]%' ORDER BY id DESC LIMIT 1"));
					if($sid)
					{
						$sql = "SELECT DISTINCT allergies.id, allergies.name, allergies.date FROM allergies INNER JOIN allergy_signs ON allergy_signs.allergy = allergies.id WHERE allergy_signs.sign = '$sid' ORDER BY date DESC";
						$result = mysqli_query($this->db, $sql);
						if(mysqli_num_rows($result))
						{
							if(mysqli_num_rows($result) === 1) $botmsg = "Am găsit următoarea alergie cu acest simptom:";
							else $botmsg = "Am găsit următoarele <strong>".mysqli_num_rows($result)."</strong> alergii cu acest simptom:";
							while($row = mysqli_fetch_assoc($result))
								$botmsg .= "<br /><a href=?p=allergy&a=".$row['id']."><strong>".$row['name']."</strong></a>";
						}
						else $botmsg = "Nu am găsit nicio alergie cu acest simptom.<br />Încearcă din nou!";
					}
					else $botmsg = "Nu am găsit niciun simptom numit astfel.<br />Încearcă din nou!";
				}
			}
			else if($cmd[0] == "cauze" || $cmd[0] == "factori")
			{
				if(empty($cmd[1])) $botmsg = "Mod de folosire:<br /><i>".$cmd[0]."</i> <strong>alergie</strong>";
				else
				{
					$aid = $this->mysqli_result(mysqli_query($this->db, "SELECT id FROM allergies WHERE name LIKE '%$cmd[1]%' ORDER BY date DESC LIMIT 1"));
					$aname = $this->mysqli_result(mysqli_query($this->db, "SELECT name FROM allergies WHERE id = '$aid'"));
					if($aid)
					{
						$sql = "SELECT causes.cause FROM allergy_causes INNER JOIN causes ON allergy_causes.cause = causes.id WHERE allergy_causes.allergy = '$aid' ORDER BY allergy_causes.cause ASC";
						$result = mysqli_query($this->db, $sql);
						if(mysqli_num_rows($result))
						{
							$botmsg = "Cauze - <strong>".$aname."</strong>:";
							while($row = mysqli_fetch_assoc($result))
								$botmsg .= "<br />".$row['cause'];
						}
						else $botmsg = "Nu s-au găsit cauze pentru această alergie.";
					}
					else $botmsg = "Nu am găsit nicio alergie numită astfel.<br />Încearcă din nou!";
				}
			}
			else if($cmd[0] == "cauza" || $cmd[0] == "cauză" || $cmd[0] == "factor")
			{
				if(empty($cmd[1])) $botmsg = "Mod de folosire:<br /><i>".$cmd[0]."</i> <strong>nume cauză</strong>";
				else
				{
					$sid = $this->mysqli_result(mysqli_query($this->db, "SELECT id FROM causes WHERE cause LIKE '%$cmd[1]%' ORDER BY id DESC LIMIT 1"));
					if($sid)
					{
						$sql = "SELECT DISTINCT allergies.id, allergies.name, allergies.date FROM allergies INNER JOIN allergy_causes ON allergy_causes.allergy = allergies.id WHERE allergy_causes.cause = '$sid' ORDER BY date DESC";
						$result = mysqli_query($this->db, $sql);
						if(mysqli_num_rows($result))
						{
							if(mysqli_num_rows($result) === 1) $botmsg = "Am găsit următoarea alergie cu această cauză:";
							else $botmsg = "Am găsit următoarele <strong>".mysqli_num_rows($result)."</strong> alergii cu această cauză:";
							while($row = mysqli_fetch_assoc($result))
								$botmsg .= "<br /><a href=?p=allergy&a=".$row['id']."><strong>".$row['name']."</strong></a>";
						}
						else $botmsg = "Nu am găsit nicio alergie cu această cauză.<br />Încearcă din nou!";
					}
					else $botmsg = "Nu am găsit nicio cauză numită astfel.<br />Încearcă din nou!";
				}
			}
			else if($cmd[0] == "ultima" || $cmd[0] == "ultimul" || $cmd[0] == "nou" || $cmd[0] == "noutati" || $cmd[0] == "noutăți" || $cmd[0] == "last")
			{
				$sql = "SELECT id, name FROM allergies ORDER BY date DESC LIMIT 1";
				$result = mysqli_query($this->db, $sql);
				$row = mysqli_fetch_assoc($result);
				$botmsg = "Ultima alergie publicată:<br /><a href=?p=allergy&a=".$row['id']."><strong>".$row['name']."</strong></a>";
			}
			else if($cmd[0] == "aleatoriu" || $cmd[0] == "aleatorie" || $cmd[0] == "random" || $cmd[0] == "oarecare")
			{
				$sql = "SELECT id, name FROM allergies ORDER BY RAND() DESC LIMIT 1";
				$result = mysqli_query($this->db, $sql);
				$row = mysqli_fetch_assoc($result);
				$botmsg = "Alergie aleatorie:<br /><a href=?p=allergy&a=".$row['id']."><strong>".$row['name']."</strong></a>";
			}
			else if($cmd[0] == "contact" || $cmd[0] == "mesaj" || $cmd[0] == "feedback")
			{
				$botmsg = "Poți lua legătura cu un administrator la fel de ușor cum poți lua legătura cu mine!<br /><a href=?p=messages>Du-te la pagina <strong>Mesaje</strong>.</a>";
			}
			else if($cmd[0] == "curata" || $cmd[0] == "curăță" || $cmd[0] == "sterge" || $cmd[0] == "șterge" || $cmd[0] == "goleste" || $cmd[0] == "golește" || $cmd[0] == "clean")
			{
				$sql = "DELETE FROM bot_messages WHERE userid = '$id'";
				mysqli_query($this->db, $sql);
				$botmsg = "Bună, ".$this->get_firstname($id)."!<br />Eu sunt AllergyBot, dar poți să-mi spui și Botzică! :)<br />Scrie <strong>ajutor</strong> pentru a afla ce pot să fac!";
			}
			else if($cmd[0] == "joke" || $cmd[0] == "gluma" || $cmd[0] == "glumă" || $cmd[0] == "banc" || $cmd[0] == "poanta" || $cmd[0] == "poantă")
			{
				$botmsg = $this->mysqli_result(mysqli_query($this->db, "SELECT joke FROM bot_jokes ORDER BY RAND() DESC LIMIT 1"));
			}
			else $botmsg = "Scuze, nu am înțeles asta... :(";
			$sql = "INSERT INTO bot_messages SET message = '$botmsg', frombot = 1, userid = '$id', date = '".date('Y-m-d H:i:s', time())."'";
			mysqli_query($this->db, $sql);
		}
		public function add_notification($id, $title, $content, $link)
		{
			return mysqli_query($this->db, "INSERT INTO notifications SET user = '$id', title = '$title', content = '$content', link = '$link', date='".date('Y-m-d H:i:s', time())."'");
		}
		public function count_notifications($id)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT COUNT(*) FROM notifications WHERE dismissed = 0 AND user = '$id'"));
		}
		public function read_notifications($id)
		{
			return mysqli_query($this->db, "UPDATE notifications SET dismissed = 1 WHERE user = '$id'");
		}
		public function get_last_user_id()
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT id FROM users ORDER BY id DESC LIMIT 1"));
		}
		public function isadmin($id)
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT admin FROM users WHERE id = '$id'"));
		}
		public function get_session()
		{
			return isset($_SESSION['allergyhelp_login']);
		}
		public function logout()
		{
			$_SESSION['allergyhelp_login'] = FALSE;
			session_destroy();
		}
	}
	class Admin extends User
	{
		public function check_login($email, $password)
		{
			$email = mysqli_real_escape_string($this->db, $email);
			$password = sha1($password);
			$sql="SELECT id, admin FROM users WHERE email='$email' AND password='$password'";

			$result = mysqli_query($this->db,$sql);
			$user_data = mysqli_fetch_array($result);
			$count_row = $result->num_rows;

			if($count_row == 1)
			{
				if(!$user_data['admin'])
					return -1;
				$_SESSION['allergyhelp_admin_login'] = true;
				$_SESSION['allergyhelp_admin_id'] = $user_data['id'];

				$ip = $this->get_ip_address();
				$id = $user_data['id'];
				$sql="INSERT INTO logins SET userid='$id', ip='$ip', date='".date('Y-m-d H:i:s', time())."'";
				mysqli_query($this->db, $sql);

				return 1;
			}
			else return 0;
		}
		public function get_logins()
		{
			$sql = "SELECT * FROM logins ORDER BY date DESC LIMIT 5";
			$result = mysqli_query($this->db, $sql);
			while($row = mysqli_fetch_assoc($result))
			{
				echo '
				<li class="list-group-item">
					<img class="avatar float-left" src="'.$this->get_avatar($row["userid"]).'" />
					<strong>'.$this->get_fullname($row["userid"]).'</strong>
					<br><small class="time text-muted">'.$this->time_passed($row["date"]).'</small>
				</li>
				';
			}
		}
		public function get_actions()
		{
			$sql = "SELECT * FROM actions ORDER BY date DESC LIMIT 5";
			$result = mysqli_query($this->db, $sql);
			while($row = mysqli_fetch_assoc($result))
			{
				echo '
				<li class="list-group-item">
					<img class="avatar float-left" src="'.$this->get_avatar($row["userid"]).'" />
					<strong>'.$this->get_fullname($row["userid"]).'</strong> '.$row["action"].'
					<br><small class="time text-muted">'.$this->time_passed($row["date"]).'</small>
				</li>
				';
			}
		}
		public function add_action($adminid, $action)
		{
			$sql="INSERT INTO actions SET userid='$adminid', action='$action', date='".date('Y-m-d H:i:s', time())."'";
			return mysqli_query($this->db, $sql);
		}
		public function add_user($adminid, $email, $pass, $lastname, $fistname)
		{
			if(!$this->isadmin($adminid)) return false;

			$email = mysqli_real_escape_string($this->db, $email);
			$lastname = mysqli_real_escape_string($this->db, $lastname);
			$firstname = mysqli_real_escape_string($this->db, $fistname);

			$pass = sha1($pass);
			$sql = "SELECT * FROM users WHERE email='$email'";

			$check =  $this->db->query($sql);
			$count_row = $check->num_rows;

			if ($count_row == 0)
			{
				$sql = "INSERT INTO users SET email='$email', password='$pass', lastname='$lastname', firstname='$firstname', regtime='".date('Y-m-d H:i:s', time())."'";
				$result = mysqli_query($this->db,$sql);

				$uid = mysqli_insert_id($this->db);
				$this->add_notification($uid, "Bine ai venit pe AllergyHelp!", "Contul tău a fost creat cu succes de către un administrator. Acum ai acces la tot conținutul site-ului. Pentru a primi notificări cu privire la anumite alergii, îți recomandăm să îți alegi simptomele și cauzele.", "?p=profile");

				if($result) $this->add_action($adminid, "a înregistrat un utilizator");
				return $result;
			}
			else return false;
		}
		public function edit_profile($id, $email, $pass = "", $lastname, $fistname)
		{
			if(!empty($pass))
			{
				$pass = sha1($pass);
				$sql = "UPDATE users SET password='$pass' WHERE id = '$id'";
				$result = mysqli_query($this->db, $sql);
				if($result) $_SESSION['allergyhelp_admin_change_pass_success'] = true;
				else $_SESSION['allergyhelp_admin_change_pass_fail'] = true;
			}
			$email = mysqli_real_escape_string($this->db, $email);
			$lastname = mysqli_real_escape_string($this->db, $lastname);
			$firstname = mysqli_real_escape_string($this->db, $fistname);

			$sql = "UPDATE users SET email='$email', lastname='$lastname', firstname='$firstname' WHERE id = '$id'";
			$result = mysqli_query($this->db,$sql);
			return $result;
		}
		public function get_users()
		{
			$sql = "SELECT * FROM users ORDER BY regtime DESC";
			$result = mysqli_query($this->db, $sql);
			while($row = mysqli_fetch_assoc($result))
			{
				echo '
				<tr>
					<td>'.$row["lastname"].' '.$row["firstname"].'</td>
					<td>'.$row["email"].'</td>
					<td>'.$row["regtime"].'</td>
					<td>';
				if($row["admin"]) echo '<a title="Înlătură drepturile de administrator" class="revoke-admin-icon" href="?revokeadmin='.$row["id"].'" /><i class="fas fa-fw fa-star"></i></a>';
				else echo '<a title="Fă-l administrator" class="make-admin-icon" href="?makeadmin='.$row["id"].'" /><i class="far fa-fw fa-star make-admin-icon"></i></a>';
				echo '
					<!--<i class="fa fa-fw fa-edit text-info"></i>
					<i class="fa fa-fw fa-trash text-danger"></i>-->
					</td>
				</tr>';
			}
		}
		public function get_admins()
		{
			$sql = "SELECT * FROM users WHERE admin = 1 ORDER BY regtime ASC";
			$result = mysqli_query($this->db, $sql);
			while($row = mysqli_fetch_assoc($result))
			{
				echo '
				<div class="admin">
					<div class="card box-shadow">
						<div class="card-body p-3">
							<img class="avatar admin-avatar" src="'.$this->get_avatar($row["id"]).'" />
							<span class="admin-name">'.$row["firstname"].' '.$row["lastname"].'</span>
							<small class="text-muted">'.$row["email"].'</small>
						</div>
					</div>
				</div>
				';
			}
		}
		public function add_sign($adminid, $sign)
		{
			if(!$this->isadmin($adminid)) return 0;

			$sign = mysqli_real_escape_string($this->db, $sign);
			$sql = "INSERT INTO signs SET sign = '$sign'";
			$result = mysqli_query($this->db, $sql);

			if($result) $this->add_action($adminid, "a adăugat un simptom");
			return $result;
		}
		public function add_cause($adminid, $cause)
		{
			if(!$this->isadmin($adminid)) return 0;

			$cause = mysqli_real_escape_string($this->db, $cause);
			$sql = "INSERT INTO causes SET cause = '$cause'";
			$result = mysqli_query($this->db, $sql);

			if($result) $this->add_action($adminid, "a adăugat o cauză");
			return $result;
		}
		public function get_signs()
		{
			$sql = "SELECT * FROM signs ORDER BY sign ASC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
					<li class="list-group-item">
						'.$row['sign'].'
						<!--<span class="badge">
							<i class="fas fa-fw fa-pencil-alt text-warning"></i>
							<i class="fa fa-fw fa-trash text-danger"></i>
						</span>-->
					</li>
					';
				}
			}
			else
				echo '
					<li class="list-group-item">
						Nu există niciun simptom adăugat!
					</li>
					';
		}
		public function get_causes()
		{
			$sql = "SELECT * FROM causes ORDER BY cause ASC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
					<li class="list-group-item">
						'.$row['cause'].'
						<!--<span class="badge">
							<i class="fas fa-fw fa-pencil-alt text-warning"></i>
							<i class="fa fa-fw fa-trash text-danger"></i>
						</span>-->
					</li>
					';
				}
			}
			else
				echo '
					<li class="list-group-item">
						Nu există nicio cauză adăugată!
					</li>
					';
		}
		public function get_allergies()
		{
			$sql = "SELECT * FROM allergies ORDER BY date DESC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
					<li class="media">
						<img class="d-none d-md-block" src="'.$this->get_allergy_cover($row['id']).'">
						<div class="media-body">
							<h5 class="mt-0 mb-1">'.$row['name'].'</h5>
							'.mb_strimwidth(strip_tags($row['content']), 0, 300, "...").'
							<hr class="my-1" />
							<small class="text-muted">
								<i class="fa fa-clock mr-2"></i>'.$this->time_passed($row['date']).'
								&nbsp&middot;&nbsp&nbsp<i class="fa fa-user mr-2"></i>'.$this->get_firstname($row['author']).' '.$this->get_lastname($row['author']).'<br />';
					$this->get_allergy_signs($row['id']);
					$this->get_allergy_causes($row['id']);
					echo '
							</small>
						</div>
					</li>
					';
				}
			}
			else
				echo '
					<li class="media">
						<div class="media-body">
							Nu există nicio alergie adăugată!
						</div>
					</li>
					';
		}
		public function get_allergy_signs($allergy)
		{
			$sql = "SELECT signs.sign FROM allergy_signs INNER JOIN signs ON allergy_signs.sign = signs.id WHERE allergy_signs.allergy = '$allergy' ORDER BY allergy_signs.sign ASC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '<div class="badge badge-danger mx-1">'.$row['sign'].'</div>';
				}
			}
			else echo '<div class="badge badge-dark mx-1">Niciun simptom selectat</div>';
		}
		public function get_allergy_causes($allergy)
		{
			$sql = "SELECT causes.cause FROM allergy_causes INNER JOIN causes ON allergy_causes.cause = causes.id WHERE allergy_causes.allergy = '$allergy' ORDER BY allergy_causes.cause ASC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					echo '<div class="badge badge-warning mx-1">'.$row['cause'].'</div>';
				}
			}
			else echo '<div class="badge badge-dark mx-1">Nicio cauză selectată</div>';
		}
		public function get_signs_causes_checkboxes()
		{
			echo '
				<div class="row">
				';
			$sql = "SELECT * FROM signs ORDER BY sign ASC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				echo '
					<div class="col-sm mt-4">
						<h3>Alege simptomele</h3>
							<hr class="mt-0 mb-3" />
								<div class="signs-causes-checkboxes">
					';
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="sign-'.$row['id'].'" name="sign_'.$row['id'].'">
							<label class="form-check-label" for="sign-'.$row['id'].'">'.$row['sign'].'</label>
						</div>
						';
				}
				echo '
						</div>
					</div>
					';
			}
			$sql = "SELECT * FROM causes ORDER BY cause ASC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				echo '
					<div class="col-sm mt-4">
						<h3>Alege cauzele</h3>
							<hr class="mt-0 mb-3" />
								<div class="signs-causes-checkboxes">
					';
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="cause-'.$row['id'].'" name="cause_'.$row['id'].'">
							<label class="form-check-label" for="cause-'.$row['id'].'">'.$row['cause'].'</label>
						</div>
						';
				}
				echo '
						</div>
					</div>
					';
			}
			echo '
				</div>
				';
		}
		public function add_allergy($adminid, $name, $content)
		{
			if(!$this->isadmin($adminid)) return 0;
			
			$name = mysqli_real_escape_string($this->db, $name);
			$content = mysqli_real_escape_string($this->db, $content);
			
			$sql = "INSERT INTO allergies SET author='$adminid', name='$name', content='$content', date='".date('Y-m-d H:i:s', time())."'";
			$result = mysqli_query($this->db,$sql);
			
        	if($result) $this->add_action($adminid, "a adăugat o alergie");
			return $result;
		}
		public function set_admin($adminid, $userid)
		{
			if(!$this->isadmin($adminid)) return 0;

			$sql = "UPDATE users SET admin = 1 WHERE id = '$userid'";
			$result = mysqli_query($this->db, $sql);

			if($result) $this->add_action($adminid, "a adăugat un administrator");
			return $result;
		}
		public function revoke_admin($adminid, $userid)
		{
			if(!$this->isadmin($adminid)) return 0;

			$sql = "UPDATE users SET admin = 0 WHERE id = '$userid'";
			$result = mysqli_query($this->db, $sql);

			if($result) $this->add_action($adminid, "a șters un administrator");
			return $result;
		}
		public function count_users()
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT COUNT(*) FROM users"));
		}
		public function count_allergies()
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT COUNT(*) FROM allergies"));
		}
		public function get_last_sign_id()
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT id FROM signs ORDER BY id DESC LIMIT 1"));
		}
		public function get_last_cause_id()
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT id FROM causes ORDER BY id DESC LIMIT 1"));
		}
		public function get_last_allergy_id()
		{
			return $this->mysqli_result(mysqli_query($this->db, "SELECT id FROM allergies ORDER BY id DESC LIMIT 1"));
		}
		public function add_sign_to_allergy($allergy, $sign)
		{
			mysqli_query($this->db, "INSERT INTO allergy_signs SET allergy='$allergy', sign='$sign'");

			$sql = "SELECT user FROM user_signs WHERE sign = '$sign'";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					$user = $row['user'];
					$link = "?p=allergy&a=".$allergy;
					$notified = $this->mysqli_result(mysqli_query($this->db, "SELECT COUNT(*) FROM notifications WHERE user = '$user' AND link = '$link'"));
					if(!$notified) $this->add_notification($user, "Alergie nouă", "A fost adăugată o alergie ce conține un simptom sau o cauză salvată de tine.", $link);
				}
			}
		}
		public function add_cause_to_allergy($allergy, $cause)
		{
			mysqli_query($this->db, "INSERT INTO allergy_causes SET allergy='$allergy', cause='$cause'");

			$sql = "SELECT user FROM user_causes WHERE cause = '$cause'";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				while($row = mysqli_fetch_assoc($result))
				{
					$user = $row['user'];
					$link = "?p=allergy&a=".$allergy;
					$notified = $this->mysqli_result(mysqli_query($this->db, "SELECT COUNT(*) FROM notifications WHERE user = '$user' AND link = '$link'"));
					if(!$notified) $this->add_notification($user, "Alergie nouă", "A fost adăugată o alergie ce conține un simptom sau o cauză salvată de tine.", $link);
				}
			}
		}
		public function admin_get_conversations()
		{
			$sql = "SELECT * FROM conversations ORDER BY date DESC";
			$result = mysqli_query($this->db, $sql);
			if(mysqli_num_rows($result))
			{
				echo '<ul class="list-group">';
				while($row = mysqli_fetch_assoc($result))
				{
					echo '
						<a class="list-group-item" href="?p=messages&m='.$row['id'].'">
							<strong>'.$row['subject'].'</strong>
							<br /><small class="text-muted"><i class="fa fa-user"></i>&nbsp;&nbsp;'.$this->get_fullname($row['userid']).'</small>&nbsp&nbsp;&middot;&nbsp;&nbsp;<small class="text-muted"><i class="fa fa-clock"></i>&nbsp;&nbsp;'.$this->time_passed($row['date']).'</small>
						</a>
						';
				}
				echo '</ul>';
			}
			else echo 'Nu există niciun mesaj de la utilizatori încă.';
		}
		public function admin_get_conversation($id, $conversation)
		{
			$sql = "SELECT subject FROM conversations WHERE id = '$conversation'";
			$result = mysqli_query($this->db, $sql);
			if(!mysqli_num_rows($result))
			{
				echo '<h3 class="title m-0">Conversația nu există!</h3><a href="?p=messages">Înapoi la mesaje</a>';
				return 0;
			}
			$conv = mysqli_fetch_assoc($result);
			echo '<h1>Mesaje <small class="text-muted">'.$conv['subject'].'</small></h1><hr class="mt-0 mb-3" />';
			$sql = "SELECT * FROM messages WHERE conversation = '$conversation' ORDER BY date ASC, id ASC";
			$result = mysqli_query($this->db, $sql);
			echo '
				<div class="msg-box">
					<div class="msg-list">
				';
			while($row = mysqli_fetch_assoc($result))
			{
				if($row['userid'] === $id) $reply_user = " reply-user";
				else $reply_user = "";
				echo '<div class="reply'.$reply_user.'">';
				if($row['userid'] !== $id)
					echo '<div class="reply-name">'.$this->get_fullname($row["userid"]).'</div>';
				echo '<img class="avatar reply-avatar" src="'.$this->get_avatar($row['userid']).'" />';
				echo '<div class="reply-text">'.$row['message'].'</div></div>';
			}
			echo '
					</div>
					<form action="" method="post" name="new_reply">
						<input class="reply-box" type="text" name="reply" placeholder="Răspunde..." required autofocus></input>
						<button type="submit" class="send-reply" name="new_reply"><i class="fa fa-paper-plane"></i></button>
					</form>
				</div>
				';
		}
		public function send_reply($id, $message, $conversation)
		{
			$message = mysqli_real_escape_string($this->db, $message);

			$uid = $this->mysqli_result(mysqli_query($this->db, "SELECT userid FROM conversations WHERE id = '$conversation'"));
			$sql = "INSERT INTO messages SET message = '$message', userid = '$id', conversation = '$conversation', date = '".date('Y-m-d H:i:s', time())."'";
			mysqli_query($this->db, $sql);
			$sql = "UPDATE conversations SET date = '".date('Y-m-d H:i:s', time())."' WHERE id = '$conversation'";
			mysqli_query($this->db, $sql);

			if($uid !== $id)
			{
				$this->add_notification($uid, "Mesaj nou", $this->get_fullname($id)." a răspuns la conversația creată de tine.", "?p=messages&m=".$conversation);
				$sql = "UPDATE conversations SET unread = 1 WHERE id = '$conversation'";
				mysqli_query($this->db, $sql);
			}
			return 1;
		}
		public function get_allergy_cover($id)
		{
			if(file_exists("../assets/img/allergies/".$id.".jpg")) $cover = $id;
			else $cover = 0;
			$source = "../assets/img/allergies/" . $cover . ".jpg?=" . filemtime('../assets/img/allergies/'.$cover.'.jpg');
			return $source;
		}
		public function get_avatar($id)
		{
			if(file_exists("../assets/img/avatars/".$id.".jpg")) $avatar = $id;
			else $avatar = 0;
			$source = "../assets/img/avatars/" . $avatar . ".jpg?=" . filemtime('../assets/img/avatars/'.$avatar.'.jpg');
			return $source;
		}
		public function get_admin_session()
		{
			return isset($_SESSION['allergyhelp_admin_login']);
		}
		public function admin_logout()
		{
			$_SESSION['allergyhelp_admin_login'] = FALSE;
			session_destroy();
		}
	}
?>
