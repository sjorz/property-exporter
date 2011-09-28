#!/bin/bash

#
#	Legacy DB Exporter script
#

#
#   Start-up is controlled by two files:
#   LOCK_FILE   If exists and script not started using 'at' then bail out
#   RUN_FILE    If does not exist bail out
#
#   Therefore, to stop backgrounding, delete the RUN_FILE
#
#   LOCK_FILE prevents the script from being started manually multiple times.
#
#   The start/stop strategy should be as follows:
#   - start:    If the RUN_FILE and LOCK_FILE both exist, report
#				task already active and bail out
#               Else:
#                   if the RUN_FILE does not exist, create it.
#                   if the LOCK_FILE exists, delete it
#                   then start exporter.sh
#   - stop:     Delete the RUN_FILE. The script will complete but will
#               not restart itself.
#	- force:	Remove the RUN_FILE and LOCK_FILE, kill all outstanding
#				jobs for our user name, and do a start sequence.
#

HOME=/home/george

LOCK_FILE=/tmp/exporter.lock
RUN_FILE=/tmp/exporter.run

#
#	If the LOCK_FILE exists, and no arg given, bail out
#

if [ -z $1 ]
then
    if [ -f $LOCK_FILE ]
    then
        echo "Script already active in background"
        exit
    fi
fi

#
#	If the RUN_FILE does not exist, bail out
#

if [ ! -f $RUN_FILE ]
then
    [ -f $LOCK_FILE ] && rm $LOCK_FILE
    exit
fi

RUNDIR=$HOME/property-exporter
SCRIPT=$RUNDIR/exporter.sh
LOG=$RUNDIR/export.log
OUT_FILE=$RUNDIR/export.json

touch $LOCK_FILE

START=`date +%s`

cd $HOME

[ -s $LOG ] && mv $LOG $LOG.bak

#
#	Start the actual export script, results to $OUT_FULE
#

############### php property-exporter/export.php $* > $OUT_FILE
############### cp -p $OUT_FILE $OUT_FILE.bak

#
#	Run the import ruby script on the export results
#

############### cat $OUT_FILE | ruby run.rb > import.log
############### cp -p import.log import.log.bak

#
#	Use 'at' to schedule the nex execution
#

END=`date +%s`
DURATION=`expr $END - $START`
STARTAT=`expr 600 - $DURATION`			# Minimum interval is 10 minutes
STARTAT=`expr $STARTAT / 60`			# Seconds -> minutes
STARTAT=`expr $STARTAT + 1`				# Add 1 minute for rounding
[ $STARTAT -lt 0 ] && STARTAT=1			# Minimum is 1 min, cant be < 0
# echo $DURATION $STARTAT
at now + "$STARTAT" minutes  2>/dev/null <<EOC
 $SCRIPT auto >$RUNDIR/exporter.out
EOC

