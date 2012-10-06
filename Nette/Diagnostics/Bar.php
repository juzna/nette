<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Diagnostics;

use Nette;



/**
 * Debug Bar.
 *
 * @author     David Grudl
 */
class Bar extends Nette\Object
{
	/** @var array */
	private $panels = array();



	/**
	 * Add custom panel.
	 * @param  IBarPanel
	 * @param  string
	 * @return Bar  provides a fluent interface
	 */
	public function addPanel(IBarPanel $panel, $id = NULL)
	{
		if ($id === NULL) {
			$c = 0;
			do {
				$id = get_class($panel) . ($c++ ? "-$c" : '');
			} while (isset($this->panels[$id]));
		}
		$this->panels[$id] = $panel;
		return $this;
	}



	/**
	 * Returns panel with given id
	 * @param  string
	 * @return IBarPanel|NULL
	 */
	public function getPanel($id)
	{
		return isset($this->panels[$id]) ? $this->panels[$id] : NULL;
	}



	/**
	 * Renders debug bar.
	 * @return void
	 */
	public function render($contentOnly = FALSE)
	{
		if (preg_match('#^Location:#im', implode("\n", headers_list()))) {
			$this->store();
			return;
		}

		$panels = $this->renderPanels();

		@session_start();
		$session = & $_SESSION['__NF']['debuggerbar'];

		foreach (array_reverse((array) $session) as $reqId => $oldpanels) {
			$panels[] = array(
				'tab' => '<span title="Previous request before redirect">previous</span>',
				'panel' => NULL,
				'previous' => TRUE,
			);
			foreach ($oldpanels as $panel) {
				$panel['id'] .= '-' . $reqId;
				$panels[] = $panel;
			}
		}
		$session = NULL;

		require __DIR__ . '/templates/bar.phtml';
	}



	/**
	 * Store panels to session for later retrieval (used in redirects and ajax)
	 */
	public function store()
	{
		@session_start();
		$session = &$_SESSION['__NF']['debuggerbar'];
		$session[] = $this->renderPanels();
	}



	/*****************  internal  *****************d*g*/



	/**
	 * Render all panels into array
	 * @internal
	 * @return array
	 */
	private function renderPanels()
	{
		$obLevel = ob_get_level();
		$panels  = array();
		foreach($this->panels as $id => $panel) {
			try {
				$panels[] = array(
					'id'    => preg_replace('#[^a-z0-9]+#i', '-', $id),
					'tab'   => $tab = (string)$panel->getTab(),
					'panel' => $tab ? (string)$panel->getPanel() : NULL,
				);
			} catch(\Exception $e) {
				$panels[] = array(
					'id'    => "error-" . preg_replace('#[^a-z0-9]+#i', '-', $id),
					'tab'   => "Error in $id",
					'panel' => '<h1>Error: ' . $id . '</h1><div class="nette-inner">' . nl2br(htmlSpecialChars($e)) . '</div>',
				);
				while(ob_get_level() > $obLevel) { // restore ob-level if broken
					ob_end_clean();
				}
			}
		}
		return $panels;
	}

}
