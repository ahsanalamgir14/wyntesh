import request from '@/utils/request'

export function getContestStats(query) {
  return request({
    url: '/user/contests',
    method: 'get',
    params: query
  })
}

export function getCurrentContest() {
  return request({
    url: '/user/contest/current',
    method: 'get'
  })
}
