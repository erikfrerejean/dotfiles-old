#!/usr/bin/php
<?php

class DotFileIterator extends FilterIterator
{
	private $ignore = array(
		'.dotfiles.esproj',
		'.DS_Store',
		'.git',
	);

	public function __construct($path = __DIR__)
	{
		parent::__construct(new DirectoryIterator($path));
	}

	public function accept()
	{
		return (
			!$this->getInnerIterator()->isDot() &&
			$this->getInnerIterator()->getExtension() != 'php' &&
			!in_array($this->getInnerIterator()->getFilename(), $this->ignore)
		) ? true : false;
	}
}

$iterator = new DotFileIterator();

// Backup
$_homeDir		= __DIR__ . '/../';
$_backupPath	= $_homeDir . '.dotfiles-' . time() . '/';
mkdir($_backupPath, 0777, true);
foreach ($iterator as $it)
{
	if (file_exists("{$_homeDir}." . $it->getFilename()))
	{
		rename("{$_homeDir}." . $it->getFilename(), "{$_backupPath}." . $it->getFilename());
	}
}

// Pull in the latest version of this repo
exec('git fetch');
exec('git checkout master');
exec('git reset --hard HEAD');
exec('git merge origin/master');

// Reset the iterator
$iterator->rewind();

// Copy all the files
foreach ($iterator as $it)
{
	if ($it->isDir())
	{
		$dest = realpath($_homeDir) . '/.' . $it->getFilename() . '/';

		if (!file_exists($dest))
		{
			mkdir($dest, 0777, true);
		}

		$iterator2 = new DotFileIterator($it->getPathname());
		foreach ($iterator2 as $it2)
		{
			$source	= realpath($it2->getPathname());
			$dest	.= $it2->getFilename();

			echo 'Copying: ' . $source . ' => ' . $dest . PHP_EOL;
			copy($source, $dest);
		}
	}
	else
	{
		$source	= realpath($it->getPathname());
		$dest	= realpath($_homeDir) . '/.' . $it->getFilename();

		echo 'Copying: ' . $source . ' => ' . $dest . PHP_EOL;
		copy($source, $dest);
	}
}

// Source
chdir('..');
exec('source .bash_profile');

echo "\033[0;31mFinished\033[0m";
