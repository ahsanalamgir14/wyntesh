import request from '@/utils/request'

export function getContestStats(query) {
  return request({
    url: '/user/contests',
    method: 'get',
    params: query
  })
}

export function getContests(query) {
  return request({
    url: '/admin/contests',
    method: 'get',
    params: query
  })
}

export function getAllContests() {
  return request({
    url: '/admin/contests/all',
    method: 'get'
  })
}

export function fetchContest(id) {
  return request({
    url: '/admin/contest/'+id,
    method: 'get'
  })
}

export function startContest(id) {
  return request({
    url: '/admin/contest/start/'+id,
    method: 'get'
  })
}

export function deleteContest(id) {
  return request({
    url: '/admin/contest/'+id+'/delete',
    method: 'delete'
  })
}


export function createContest(data) {
  return request({
    url: '/admin/contest',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateContest(data) {
  return request({
    url: '/admin/contest/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function getContestRewards(query) {
  return request({
    url: '/admin/contest/special/rewards',
    method: 'get',
    params: query
  })
}

export function createSpecialReward(data) {
  return request({
    url: '/admin/contest/special/reward',
    method: 'post',
    data
  })
}

export function updateSpecialReward(data) {
  return request({
    url: '/admin/contest/special/reward/update',
    method: 'post',
    data
  })
}

export function deleteContestSpecialReward(id) {
  return request({
    url: '/admin/contest/special/reward/'+id+'/delete',
    method: 'delete'
  })
}

export function getContestBanners(query) {
  return request({
    url: '/admin/contest/banners',
    method: 'get',
    params: query
  })
}

export function createBanner(data) {
  return request({
    url: '/admin/contest/banner',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function deleteBanner(id) {
  return request({
    url: '/admin/contest/banner/'+id+'/delete',
    method: 'delete'
  })
}