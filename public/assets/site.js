(()=>{
  const keys=['utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','gbraid','wbraid','ttclid','li_fat_id'];
  const qs=new URLSearchParams(location.search);
  for(const k of keys){const v=qs.get(k);if(v)sessionStorage.setItem('salwa_'+k,v)}
  if(!sessionStorage.getItem('salwa_session_id'))sessionStorage.setItem('salwa_session_id',(crypto.randomUUID?crypto.randomUUID():Date.now().toString(36)+Math.random().toString(36).slice(2)));

  const pushEvent=(event,detail={})=>{
    window.dataLayer=window.dataLayer||[];
    window.dataLayer.push({event,...detail});
  };

  document.addEventListener('click',(e)=>{
    const a=e.target.closest&&e.target.closest('a[href]');
    if(!a)return;
    const href=String(a.getAttribute('href')||'').trim();
    const declared=String(a.dataset.event||'');
    const common={click_url:href,click_text:(a.textContent||'').trim().slice(0,120),page_path:location.pathname};
    if(declared==='click_call'||href.toLowerCase().startsWith('tel:')){
      pushEvent('click_call',common);
      return;
    }
    if(declared==='click_whatsapp'||declared==='whatsapp_after_form'||/^(https?:\/\/)?(wa\.me|api\.whatsapp\.com|www\.whatsapp\.com|whatsapp\.com)\//i.test(href)){
      pushEvent('click_whatsapp',{...common,source_event:declared||'whatsapp_link'});
    }
  },true);

  const form=document.querySelector('#lead-form');
  if(!form)return;
  const err=form.querySelector('.form-error');
  form.addEventListener('submit',async(e)=>{
    e.preventDefault();
    err.textContent='';
    form.querySelectorAll('.is-invalid').forEach(el=>el.classList.remove('is-invalid'));
    const fd=new FormData(form);
    const body={name:String(fd.get('name')||'').trim(),phone:String(fd.get('phone')||'').trim(),service:String(fd.get('service')||''),message:String(fd.get('message')||'').trim(),consent:fd.get('consent')==='1',landing_page_id:form.dataset.pageId||'',landing_url:location.href.split('#')[0],referrer:document.referrer,session_id:sessionStorage.getItem('salwa_session_id')||''};
    for(const k of keys)body[k]=sessionStorage.getItem('salwa_'+k)||'';
    const btn=form.querySelector('button[type="submit"]');
    btn.disabled=true;
    btn.textContent='جارٍ الإرسال...';
    try{
      const r=await fetch('/api/lead/',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(body)});
      const data=await r.json();
      if(!r.ok||!data.ok){
        if(data.errors){
          Object.keys(data.errors).forEach(k=>{const el=form.elements[k];if(el)el.classList.add('is-invalid')});
          err.textContent=Object.values(data.errors)[0]||'راجع البيانات المطلوبة.';
        }else err.textContent='تعذر إرسال الطلب الآن. يمكنك التواصل عبر واتساب أو الاتصال.';
        return;
      }
      const successDetail={lead_id:data.lead_id||'',page_id:body.landing_page_id||'',service:body.service||'',page_path:location.pathname};
      pushEvent('lead_form_success',successDetail);
      window.dispatchEvent(new CustomEvent('lead_form_success',{detail:successDetail}));
      setTimeout(()=>{location.assign('/شكرا/?ref='+encodeURIComponent(data.ref||''))},500);
    }catch(_){
      err.textContent='تعذر إرسال الطلب الآن. يمكنك التواصل عبر واتساب أو الاتصال.';
    }finally{
      btn.disabled=false;
      btn.textContent='إرسال الطلب';
    }
  });
})();
