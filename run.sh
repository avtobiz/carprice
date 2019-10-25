echo "Start command" >> log.txt
date >> log.txt
echo "Execute `create-job-command`.." >> log.txt
bin/console create-job
echo "Execute `execute-job`..." >> log.txt
bin/console execute-job
echo "Execute `export`..." >> log.txt
bin/console export
echo "End command" >> log.txt
date >> log.txt
echo "--" >> log.txt