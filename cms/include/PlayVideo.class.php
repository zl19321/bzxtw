<?php
class PlayVideo
{
	var $Dir;
	var $FileName   = '';
	var $Width 		= 490;
	var $Height 	= 340;
	var $FileType 	= '';
	//PlayFlv 使用的 flowplayer 需要是js和swf路径 
	public $flowplayer_js_path = '';
	public $flowplayer_swf_path = '';
	
	public function __construct($dir='',$Width=0,$Height=0)
	{
		if($dir    != '')  $this->Dir    = $dir;
		if($Width  != 0 )  $this->Width  = $Width;
		if($Height != 0 )  $this->Height = $Height;
		$this->flowplayer_js_path = __ROOT__ . '/public/statics/flowplayer/flowplayer-3.2.4.min.js';
		$this->flowplayer_swf_path = __ROOT__ . '/public/statics/flowplayer/flowplayer-3.2.4.swf';
	}
	/**
	* 播放文件
	*
	*/
	public function Play($FileName = '')
	{
		if($FileName != '') $this->FileName = $FileName;
		
		$this->FileType = strtolower(substr(strrchr($this->FileName,'.'),1));
	
		switch($this->FileType)
		{
			case "avi":
				$HtmStr = $this->PlayAvi();
				break;
			case "mpg":
				$HtmStr = $this->PlayMpg();
				break;
			case "rm":
				$HtmStr = $this->PlayRm();
				break;
			case "wma":
				$HtmStr = $this->PlayWma();
				break;
			case "swf":
				$HtmStr = $this->PlaySwf();
				break;
			case "flv":
				$HtmStr = $this->PlayFlv();
				break;
			default:
				$HtmStr = $this->PlayOther();
				break;
		}
		return $HtmStr;
	}
	//播放AVI
	public function PlayAvi()
	{
		$HtmStr = '<object id="video" width="'.$this->Width.'" height="'.$this->Height.'" border="0" classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA">
					<param name="ShowDisplay" value="0">
					<param name="ShowControls" value="1">
					<param name="AutoStart" value="1">
					<param name="AutoRewind" value="0">
					<param name="PlayCount" value="0">
					<param name="Appearance value="0 value=""">
					<param name="BorderStyle value="0 value=""">
					<param name="MovieWindowHeight" value="240">
					<param name="MovieWindowWidth" value="320">
					<param name="FileName" value="'.$this->Dir.$this->FileName.'">
					<embed width="'.$this->Width.'" height="'.$this->Height.'" border="0" showdisplay="0" showcontrols="1" autostart="1" autorewind="0" playcount="0" moviewindowheight="240" moviewindowwidth="320" filename="'.$this->Dir.$this->FileName.'" src="'.$this->FileName.'">
					</embed> 
					</object>';
		return $HtmStr;
	}
	//播放AVI
	public function PlayMpg()
	{
		$HtmStr = '<object classid="clsid:05589FA1-C356-11CE-BF01-00AA0055595A" id="ActiveMovie1" width="'.$this->Width.'" height="'.$this->Height.'">
					<param name="Appearance" value="0">
					<param name="AutoStart" value="1">
					<param name="AllowChangeDisplayMode" value="-1">
					<param name="AllowHideDisplay" value="0">
					<param name="AllowHideControls" value="-1">
					<param name="AutoRewind" value="-1">
					<param name="Balance" value="0">
					<param name="CurrentPosition" value="0">
					<param name="DisplayBackColor" value="0">
					<param name="DisplayForeColor" value="16777215">
					<param name="DisplayMode" value="0">
					<param name="Enabled" value="-1">
					<param name="EnableContextMenu" value="-1">
					<param name="EnablePositionControls" value="-1">
					<param name="EnableSelectionControls" value="0">
					<param name="EnableTracker" value="-1">
					<param name="Filename" value="'.$this->Dir.$this->FileName.'" valuetype="ref">
					<param name="FullScreenMode" value="0">
					<param name="MovieWindowSize" value="0">
					<param name="PlayCount" value="1">
					<param name="Rate" value="1">
					<param name="SelectionStart" value="-1">
					<param name="SelectionEnd" value="-1">
					<param name="ShowControls" value="-1">
					<param name="ShowDisplay" value="-1">
					<param name="ShowPositionControls" value="0">
					<param name="ShowTracker" value="-1">
					<param name="Volume" value="-480">
					</object>';
		return $HtmStr;
	}
	//播放flash 
	public function PlaySwf()
	{
		$HtmStr = '<OBJECT CLASSID="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" WIDTH="'.$this->Width.'" HEIGHT="'.$this->Height.'">
					<PARAM NAME=MOVIE VALUE="'.$this->Dir.$this->FileName.'">
					<PARAM NAME=PLAY VALUE=TRUE>
					<PARAM NAME=LOOP VALUE=TRUE>
					<PARAM NAME=QUALITY VALUE=HIGH>
					<EMBED SRC="'.$this->Dir.$this->FileName.'" WIDTH="'.$this->Width.'" HEIGHT="'.$this->Height.'" PLAY=TRUE LOOP=TRUE QUALITY=HIGH>
					</embed>
					</object>';
		return $HtmStr;
	}
	//播放flash 
	public function PlayFlv(){
		$HtmStr = '<script type="text/javascript" src="'.$this->flowplayer_js_path.'"></script>';
		$HtmStr .= '<a href="'.$this->Dir.$this->FileName.'" style="display:block;width:'.$this->Width.'px;height:'.$this->Height.'px" id="flowplayer"></a> 
		<script type="text/javascript">
			flowplayer("flowplayer", "'.$this->flowplayer_swf_path.'");
		</script>';
		return $HtmStr;
	}
	//播放rm格式
	public function PlayRm()
	{
		$HtmStr = '<OBJECT ID=video1 CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" HEIGHT="'.$this->Height.'" WIDTH="'.$this->Width.'">
					<param name="_ExtentX" value="9313">
					<param name="_ExtentY" value="7620">
					<param name="AUTOSTART" value="1">
					<param name="SHUFFLE" value="0">
					<param name="PREFETCH" value="0">
					<param name="NOLABELS" value="0">
					<param name="SRC" value="'.$this->Dir.$this->FileName.'";>
					<param name="CONTROLS" value="ImageWindow">
					<param name="CONSOLE" value="Clip1">
					<param name="LOOP" value="0">
					<param name="NUMLOOP" value="0">
					<param name="CENTER" value="0">
					<param name="MAINTAINASPECT" value="0">
					<param name="BACKGROUNDCOLOR" value="#000000"><embed SRC type="audio/x-pn-realaudio-plugin" CONSOLE="Clip1" CONTROLS="ImageWindow" HEIGHT="288" WIDTH="352" AUTOSTART="false">';
		return $HtmStr;
	}
	//播放wma格式
	public function PlayWma()
	{
		$HtmStr = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" id="MediaPlayer1" > 
					<param name="Filename" value="'.$this->Dir.$this->FileName.'"> 
					<!--你文件的位置-->
					<param name="PlayCount" value="0">
					<!--控制重复次数: “x”为几重复播放几次; x=0，无限循环。--> 
					<param name="AutoStart" value="1">
					<!--控制播放方式: x=1，打开网页自动播放; x=0，按播放键播放。--> 
					<param name="ClickToPlay" value="1">
					<!--控制播放开关: x=1，可鼠标点击控制播放或暂停状态; x=0，禁用此功能。-->
					<param name="DisplaySize" value="0">
					<!--控制播放画面: x=0，原始大小; x=1，一半大小; x=2，2倍大小。--> 
					<param name="EnableFullScreen Controls" value="1">
					<!--控制切换全屏: x=1，允许切换为全屏; x=0，禁用此功能。--> 
					<param name="ShowAudio Controls" value="1">
					<!--控制音量: x=1，允许调节音量; x=0，禁止音量调节。-->
					<param name="EnableContext Menu" value="1">
					<!--控制快捷菜单: x=1，允许使用右键菜单; x=0，禁用右键菜单。--> 
					<param name="ShowDisplay" value="1">
					<!--控制版权信息: x=1，显示电影及作者信息;x=0，不显示相关信息-->
				  </object >';
		return $HtmStr;
	}
	//播放其他文件
	public function PlayOther()
	{
		$HtmStr = '<object id="NSPlay" width="'.$this->Width.'" height="'.$this->Height.'" classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,5,715" standby="Loading Microsoft Windows Media Player components..." type="application/x-oleobject" align="right" hspace="0">
	                        <!-- ASX File Name -->
	                        <param name="AutoRewind" value="1" />
	                        <param name="FileName" value="'.$this->Dir.$this->FileName.'" />
	                        <!-- Display Controls -->
	                        <param name="ShowControls" value="1" />
	                        <!-- Display Position Controls快进 -->
	                        <param name="ShowPositionControls" value="0" />
	                        <!-- Display Audio Controls -->
	                        <param name="ShowAudioControls" value="1" />
	                        <!-- Display Tracker Controls 拖动定位-->
	                        <param name="ShowTracker" value="1" />
	                        <!-- Show Display -->
	                        <param name="ShowDisplay" value="0" />
	                        <!-- Display Status Bar显示信息 -->
	                        <param name="ShowStatusBar" value="0" />
	                        <!-- Diplay Go To Bar -->
	                        <param name="ShowGotoBar" value="0" />
	                        <!-- Display Controls -->
	                        <param name="ShowCaptioning" value="0" />
	                        <!-- Player Autostart -->
	                        <param name="AutoStart" value="1" />
	                        <!-- Animation at Start -->
	                        <param name="Volume" value="-500" />
	                        <param name="AnimationAtStart" value="0" />
	                        <!-- Transparent at Start -->
	                        <param name="TransparentAtStart" value="0" />
	                        <!-- Do not allow a change in display size -->
	                        <param name="AllowChangeDisplaySize" value="0" />
	                        <!-- Do not allow scanning -->
	                        <param name="AllowScan" value="0" />
	                        <!-- Do not show contect menu on right mouse click -->
	                        <param name="EnableContextMenu" value="0" />
	                        <!-- Do not allow playback toggling on mouse click -->
	                        <param name="ClickToPlay" value="0" />
	                      </object>';
		return $HtmStr;
	}
} //end class