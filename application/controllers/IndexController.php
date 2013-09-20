<?php
use Phalcon\Mvc\Controller;

class IndexController extends Controller {
	
	public function indexAction() {
		$source = file_get_contents(TMP_PATH . DIRECTORY_SEPARATOR . 'killmail1');
		
		$mail = new Eveboard\Killmail\Parser($source);
		
		$kill = Kills::findByHash($mail->getHash());
		
		if ($kill !== false) {
			return;
		}
		
		$kill = new Kills();
		
		$kill->committed    = $mail->getDatetime();
		$kill->security     = $mail->getSecurityLevel();
		$kill->damage_taken = $mail->getTakenDamage();
		$kill->kill_hash    = $mail->getHash();
		$kill->mail_source  = gzcompress($source, 1);
		
		// Resolve player
		$playerName = $mail->getVictimName();
		
		if ($playerName !== null) {
			$player = Players::findByName($playerName);

			if ($player === false) {
				$player = new Players();
				$player->name = $playerName;
				$player->save();
			}

			$kill->player_id = $player->player_id;
		}
		
		// Resolve aliance
		$allianceName = $mail->getAllianceName();
		
		if ($allianceName !== null) {
			$alliance = Alliances::findByTitle($allianceName);

			if ($alliance === false) {
				$alliance = new Alliances();
				$alliance->title = $allianceName;
				$alliance->save();
			}
			
			$kill->alliance_id = $alliance->alliance_id;
		}
		
		// Resolve corporation
		$corpName = $mail->getCorpName();
		
		if ($corpName !== null) {
			$corp = Corps::findByTitle($corpName);

			if ($corp === false) {
				$corp = new Corps();
				$corp->title = $corpName;
				$corp->save();
			}
			
			$kill->corp_id = $corp->corp_id;
		}
		
		// Resolve item
		
		$kill->item_id = 1;
		
		// Resolve system
		
		$kill->system_id = 1;
		
		$kill->save();
	}
	
}