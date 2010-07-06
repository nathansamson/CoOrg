i=0
B=$@
for p in $@; do
	i=$(( $i + 1))
	if [ $p == "--coorgconfig" ]; then
		j=$(( $i+1 ))
		export COORG_CONFIGFILE=${!j}
		A=( $@ )
		j=$(( $i - 1))
		B=( "${A[@]:0:$j}" )
	fi
done

/usr/bin/php /usr/bin/phpunit --configuration plugins/phpunit.xml ${B[@]} plugins/
