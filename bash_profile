# Load ~/.bash_prompt, ~/.exports, ~/.aliases and ~/.functions
for file in ~/.{bash_prompt,exports,aliases,functions}; do
	[ -r "$file" ] && source "$file"
done
unset file

# Include personal bin folder in the executable path
if [ -d ~/bin ] ; then
	PATH="~/bin:${PATH}"
fi

# Include subfiles
for file in ~/.bash/*; do
	if [ -f $file ]
	then
		. $file
	fi
done
