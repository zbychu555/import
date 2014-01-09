<?php
namespace TYPO3\MacopediaImport\Controller;

	/***************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2013
	 *  All rights reserved
	 *
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as published by
	 *  the Free Software Foundation; either version 3 of the License, or
	 *  (at your option) any later version.
	 *
	 *  The GNU General Public License can be found at
	 *  http://www.gnu.org/copyleft/gpl.html.
	 *
	 *  This script is distributed in the hope that it will be useful,
	 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *  GNU General Public License for more details.
	 *
	 *  This copyright notice MUST APPEAR in all copies of the script!
	 ***************************************************************/

/**
 *
 *
 * @package macopedia_import
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class CSVController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {


	/**
	 * userRepository
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
	 */
	protected $userRepository;

	/**
	 * injectUserRepository
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository $userRepository
	 * @return void
	 */
	public function injectUserRepository( \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository $userRepository) {
		$this->userRepository = $userRepository;
	}

	/**
	 * userGroupRepository
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
	 */
	protected $userGroupRepository;
	/**
	 * injectUserGroupRepository
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository $userGroupRepository
	 * @return void
	 */
	public function injectUserGroupRepository( \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository $userGroupRepository) {
		$this->userGroupRepository = $userGroupRepository;
	}


	/**
	 * action import
	 *
	 * @return void
	 */
	public function importAction() {

		if( $this->request->getArguments()) {
			$file = $this->request->getArgument('file');

			$categories = $this->request->getArgument('category');
			$pid = $this->request->getArgument('pid');
			$file = fopen( $file['tmp_name'], 'r');

			$userGroups = array();
			$categories = explode(',', $categories);
			foreach ($categories as $category) {
				$userGroups[] = $this->userGroupRepository->findByUid($category);
			}
			while( $data = fgetcsv( $file, 10000, ','))
			{
				$user = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Domain\Model\FrontendUser');
				//$user = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\MacopediaImport\Domain\Model\User');

				$user->setUsername($data[0]);
				if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
					$saltedpasswordsInstance = \tx_saltedpasswords_salts_factory::getSaltingInstance();
					$encryptedPassword = $saltedpasswordsInstance->getHashedPassword($data[1]);
					$user->setPassword( $encryptedPassword);
				}


				foreach ($userGroups as $userGroup) {
					$user->addUsergroup($userGroup);
				}
				$user->setPid($pid);
				$this->userRepository->add( $user);
			}
			fclose( $file);

			$this->view->assign('information', 'Loaded');
		}
	}
}