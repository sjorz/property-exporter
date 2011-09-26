#
#	Exporter script
#

HOME=~/property-importer/property-exporter
LOG=$HOME/export.log
OUTPUT=$HOME/export.json

cd $HOME

[ -s $LOG ] && mv $LOG $LOG.bak
php property-exporter/export.php $* > export.json
cp -p $OUTPUT $OUTPUT.bak

cd ..

cat $OUTPUT | ruby run.rb > run.log
cp -p run.log run.log.bak
