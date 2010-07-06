if [ -f 'deployment.config' ]; then
	. deployment.config
fi

updates=1
php tools/generateresource.php $COORGNG_DEPLOY_THEMES && updates=0

if [ $updates -eq 1 ]; then
	echo "Update versions"
	exit;
fi

function prepare {
	cwd=`pwd`
	echo "Entering $1"
	cd "$1/static"

	mkdir -p "$cwd/$2"
	old="N"
	if [ -f "$cwd/$2/__md5s" ]; then
		mv "$cwd/$2/__md5s" "$cwd/$2/__md5s.old"
		old="Y"
	fi
	touch "$cwd/$2/__md5s"

	for script in `find -iname '*.js' -or -iname '*.css'`; do
		mkdir -p "$cwd/$2`dirname $script`"
		md5=`md5sum "$script" | cut -d' ' -f1`
		if [ $old = "Y" ]; then
			oldmd5=`cat "$cwd/$2/__md5s.old" | grep $script | cut -d'	' -f2`
		else
			oldmd5="nomd5"
		fi;
		if [ "$oldmd5" != "$md5" ]; then
			echo "Updating $script"
			yui-compressor "$script" -o "$cwd/$2$script"
		fi
		echo "$script	$md5" >> "$cwd/$2/__md5s"
	done
	
	for script in `find -not -iname '*.js' -and -not -iname '*.css' -type f`; do
		mkdir -p "$cwd/$2`dirname $script`"
		cp "$script" "$cwd/$2$script"
	done
	
	cd $cwd
}

if [ ! -d ".tmpstaticoutput" ]; then
	mkdir .tmpstaticoutput
fi

prepare './' ".tmpstaticoutput/_root/"

for i in `ls plugins`; do
	if [ -d "plugins/$i/static" ]; then	
		prepare "plugins/$i" ".tmpstaticoutput/$i/"
	fi
done

if [ "$COORGNG_DEPLOY_SERVER" != "" ]; then
	echo "Mirroring"
	cd .tmpstaticoutput
	lftp -c "open $COORGNG_DEPLOY_USER@$COORGNG_DEPLOY_SERVER; cd $COORGNG_DEPLOY_PATH; mirror -R . ."
else
	echo "No deployment destination"
fi
