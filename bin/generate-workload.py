#!/usr/bin/env python

import random

# time a run will take in seconds
time = 20 * 60

normal_jobs_submitted_per_second = 5
normal_job_submit_interval = (1 / normal_jobs_submitted_per_second)
normal_job_submit_interval_mus = normal_job_submit_interval * 1000000

burst_jobs_submitted_per_second = 5 * normal_jobs_submitted_per_second
burst_job_submit_interval = (1 / burst_jobs_submitted_per_second)
burst_job_submit_interval_mus = burst_job_submit_interval * 1000000

timeLeft = time

## 1 minute of normal workload
#while timeLeft > (time - 60):
#    #print('{"user_id":"normal","image_path":"\/path\/to\/new\/pic.png","size":' + str(random.normalvariate(200000, 20000)) + ',"submitTime":null,"jobInterupt":200000})')
#    print('{"user_id":"normal","image_path":"\/path\/to\/new\/pic.png","size":' + str(random.normalvariate(normal_job_submit_interval_mus, normal_job_submit_interval_mus/10)) + ',"submitTime":null,"jobInterupt":' + str(normal_job_submit_interval_mus) + '}')
#    timeLeft = timeLeft - normal_job_submit_interval
#
## 1 minute of normal workload
#while timeLeft > (time - 120):
#    #print('{"user_id":"normal","image_path":"\/path\/to\/new\/pic.png","size":' + str(random.normalvariate(200000, 20000)) + ',"submitTime":null,"jobInterupt":200000})')
#    print('{"user_id":"burst","image_path":"\/path\/to\/new\/pic.png","size":' + str(random.normalvariate(burst_job_submit_interval_mus, burst_job_submit_interval_mus/10)) + ',"submitTime":null,"jobInterupt":' + str(burst_job_submit_interval_mus) + '}')
#    timeLeft = timeLeft - burst_job_submit_interval

# rest of the time submit normally
while timeLeft > 0:
    #print('{"user_id":"normal","image_path":"\/path\/to\/new\/pic.png","size":' + str(random.normalvariate(200000, 20000)) + ',"submitTime":null,"jobInterupt":200000})')
    print('{"user_id":"normal","image_path":"\/path\/to\/new\/pic.png","size":' + str(random.normalvariate(normal_job_submit_interval_mus, normal_job_submit_interval_mus/10)) + ',"submitTime":null,"jobInterupt":' + str(normal_job_submit_interval_mus) + '}')
    timeLeft = timeLeft - normal_job_submit_interval
