<?php
interface MailProvider {
	
	/**
	 * @param MailTemplate $template
	 */
	public function sendMail($template);
}