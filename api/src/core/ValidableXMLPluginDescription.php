<?php

namespace API\Core;

class ValidableXMLPluginDescription {
	public $contents;
	public $parsable = false;
	public $validated = false;
	public $errors;

	public $requiredFields = [
		"name",
		"key",
		"state",
		"logo",
		"description",
		"homepage",
		"download",
		"issues",
		"readme",
		"authors",
		"versions",
		"langs",
		"license",
		"tags"
	];

	public function validateName() {
		if (sizeof($this->contents->name) != 1 ||
			sizeof($this->contents->name->children()) != 0) {
			$this->errors[] = "<name> should be a singular field";
			return false;
		}
		return true;
	}

	public function validateKey() {
		if (sizeof($this->contents->key) != 1 ||
			sizeof($this->contents->key->children()) != 0) {
			$this->errors[] = "<key> should be a singular field";
			return false;
		}
		return true;
	}

	public function validateState() {
		if (sizeof($this->contents->state) != 1 ||
			sizeof($this->contents->state->children()) != 0 ||
			!in_array((string)$this->contents->state, ['stable', 'unstable', 'beta', 'alpha']))
		{
			$this->errors[] = "<state> should be 'stable', 'unstable', 'beta' or 'alpha'";
			return false;
		}
		return true;
	}

	public function validateLogo() {
		if (sizeof($this->contents->logo) != 1 ||
			sizeof($this->contents->logo->children()) != 0) {
			$this->errors[] = "<logo> should be a singular field";
			return false;
		}
		return true;
	}

	public function validateDescription() {
		if (sizeof($this->contents->description) != 1 ||
			sizeof($this->contents->description->children()) != 2) {
			$this->errors[] = "<description> should contain <short> and <long>";
			return false;
		}

		foreach ($this->contents->description->children() as $type => $langs) {
			if (!in_array($type, ['long', 'short'])) {
				$this->errors[] = "<description> should contain <short> and <long> only";
				return false;
			}
			if(sizeof($langs->children()) < 1) {
				$this->errors[] = "each <short> and <long> should have at least one <lang> inside";
				return false;
			}
		}
 
		return true;
	}

	public function validateHomepage() {
		if (sizeof($this->contents->homepage) != 1 ||
			sizeof($this->contents->homepage->children()) != 0) {
			$this->errors[] = "<homepage> should be a singular field";
			return false;
		}
		return true;
	}

	public function validateDownload() {
		if (sizeof($this->contents->download) != 1 ||
			sizeof($this->contents->download->children()) != 0) {
			$this->errors[] = "<download> should be a singular field";
			return false;
		}
		return true;
	}

	public function validateIssues() {
		if (sizeof($this->contents->issues) != 1 ||
			sizeof($this->contents->issues->children()) != 0) {
			$this->errors[] = "<issues> should be a singular field";
			return false;
		}
		return true;
	}

	public function validateReadme() {
		if (sizeof($this->contents->readme) != 1 ||
			sizeof($this->contents->readme->children()) != 0) {
			$this->errors[] = "<readme> should be a singular field";
			return false;
		}
		return true;
	}

	public function validateAuthors() {
		if (sizeof($this->contents->authors) != 1 ||
			sizeof($this->contents->authors->children()) < 1) {
			$this->errors[] = "<authors> should contain at least one <author>";
			return false;
		}

		foreach ($this->contents->authors->children() as $author) {
			if (sizeof($author->children()) != 0) {
				$this->errors[] = "<author> should be a singular field";
				return false;
			}
		}

		return true;
	}

	public function validateVersions() {
		if (sizeof($this->contents->versions) != 1 ||
			sizeof($this->contents->versions->children()) < 1) {
			$this->errors[] = "<versions> should contain at least one <version>";
			return false;
		}

		foreach ($this->contents->versions->children() as $version) {
			foreach ($version->children() as $prop => $val) {
				if (!in_array($prop, ['num', 'compatibility'])) {					
					$this->errors[] = "<version> should contain only <num> and <compatibility>";
					return false;
				}
			}
		}
		return true;
	}

	public function validateLangs() {
		if (sizeof($this->contents->langs) != 1 ||
			sizeof($this->contents->langs->children()) < 1) {
			$this->errors[] = "<langs> should contain at least one <lang>";
			return false;
		}

		foreach ($this->contents->langs->children() as $tag => $lang) {
			if ($tag != 'lang') {
				$this->errors[] = "<langs> should contain only <lang> tags";
				return false;
			}

			if (sizeof($lang->children()) != 0) {
				$this->errors[] = "<lang> should be a singular field";
				return false;
			}
		}
		return true;
	}

	public function validateLicense() {
		if (sizeof($this->contents->license) != 1 ||
			sizeof($this->contents->license->children()) != 0) {
			$this->errors[] = "<license> should be a singular field";
			return false;
		}
		return true;
	}

	public function validateTags() {
		if (sizeof($this->contents->tags) != 1 ||
			sizeof($this->contents->tags->children()) < 1) {
			$this->errors[] = "<tags> should contain at least one <[lang]>";
			return false;
		}

		foreach ($this->contents->tags->children() as $lang => $tags) {
			foreach ($tags->children() as $prop => $tag) {
				if ($prop != 'tag') {
					$this->errors[] = "<[lang]> should contain only <tag> tags";
					return false;
				}
				if (sizeof($tag->children()) != 0) {
					$this->errors[] = "<tag> should be a singular field";
					return false;
				}
			}
		}
		return true;
	}

	public function validateScreenshots() {
		foreach ($this->contents->screenshots as $tag => $screenshot) {
			if ($tag != 'screenshot') {
				$this->errors[] = "<screenshots> should contain only <screenshot> tags";
				return false;
			}
			if (sizeof($screenshot->children()) != 0) {
				$this->errors[] = "<screenshot> should be a singular field";
				return false;
			}
		}
	}

	public function __construct($contents) {
		$this->contents = @simplexml_load_string($contents);
		if ($this->contents)
			$this->parsable = true;
	}

	public function allFieldsOK() {
		foreach ($this->contents->children() as $tag => $node) {
			$methodName = 'validate'.strtoupper($tag[0]).substr($tag,1);
			if (property_exists('ValidableXMLPluginDescription', $methodName)) {
				if (!$this->$methodName())
					return false;
			}
		}
		return true;
	}

	public function hasAllRequiredFields() {
		foreach($this->requiredFields as $field) {
			if (sizeof($this->contents->$field) < 1) {
				$this->errors[] = "missing mandatory <".$field.">";
				return false;
			}
		}
		return true;
	}

	public function isValid() {
		return ($this->parsable &&
				$this->allFieldsOK() &&
			    $this->hasAllRequiredFields());
	}
}