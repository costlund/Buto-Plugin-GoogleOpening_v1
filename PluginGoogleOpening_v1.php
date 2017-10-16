<?php
/**
 * <p>Read a Google calendar and display text content in Bootstrap list-group.
 */
class PluginGoogleOpening_v1{
  /**
   * <p>Headline is Open/Close.
   * <p>Text 1: We are open now until [end_time].
   * <p>Text 1: We have closed at the moment.
   * <p>Text 2: Next opening is about [next_hours] hours at [next_time].
   * <p>Text 2: Next opening is about [next_days] days at [next_time].
   * <p>Supporting i18n.
   */
  public static function widget_opening($data){
    /**
     * 
     */
    wfPlugin::includeonce('wf/array');
    $data = new PluginWfArray($data);
    /**
     * Data from Google Calendar url.
     */
    if($data->get('data/google_calendar')){
      $calendar = PluginGoogleOpening_v1::getGoogleCalendar($data->get('data/google_calendar'));
      /**
       * 
       */
      $shop = new PluginWfArray();
      $shop->set('end_text', null);
      $shop->set('end_description', null);
      $shop->set('end', null);
      $shop->set('end_hours', null);
      $shop->set('end_time', null);
      $shop->set('next_description', null);
      $shop->set('next', null);
      $shop->set('next_hours', null);
      $shop->set('next_days', null);
      $shop->set('next_time', null);
      $shop->set('next_date', null);
      $shop->set('now', (new DateTime())->format('Y-m-d H:i:s'));
      $shop->set('date', (new DateTime())->format('Y-m-d'));
      $now = strtotime($shop->get('now'));
      foreach ($calendar->get('event') as $key => $value) {
        $item = new PluginWfArray($value);
        if($item->get('DTSTART')){
          $start = strtotime(substr($item->get('DTSTART'), 0, 16));
          $end = strtotime(substr($item->get('DTEND'), 0, 16));
          if($start < $now && $end > $now){
            $shop->set('end', date('Y-m-d H:i:s', $end));
            $shop->set('end_hours', round(($end - $now)/60/60, 1));
            $shop->set('end_time', date('H:i', $end));
            $shop->set('end_text', 'Open');
            $shop->set('end_description', 'We are open now until [end_time].');
          }
          if($start > $now){
            if(!$shop->get('timecheck') || $shop->get('timecheck') > $start){
              $shop->set('timecheck', $start);
              $shop->set('next', date('Y-m-d H:i:s', $start));
              $shop->set('next_hours', round(($start - $now)/60/60, 1));
              
              $date1 = new DateTime();
              $date1->setDate(date('Y', $start), date('m', $start), date('d', $start));
              $date2 = new DateTime();
              $date2->setDate(date('Y', $now), date('m', $now), date('d', $now));
              $interval = date_diff($date1, $date2);
              $shop->set('next_days', $interval->format('%a'));
              
              $shop->set('next_time', date('H:i', $start));
              $shop->set('next_date', date('Y-m-d', $start));
              if($shop->get('next_days') == 0){
                $shop->set('next_description', 'Next opening is about [next_hours] hours at [next_time].');
              }else{
                $shop->set('next_description', 'Next opening is about [next_days] days at [next_time].');
              }
            }
          }
        }
      }
      if($shop->get('end') == null){
        $shop->set('end_text', 'Closed');
        $shop->set('end_description', 'We have closed at the moment.');
      }
      /**
       * i18n
       */
      wfPlugin::includeonce('i18n/translate_v1');
      $i18n = new PluginI18nTranslate_v1();
      /**
       * Remove timecheck.
       */
      $shop->setUnset('timecheck');
      $element = array();
      $element[] = wfDocument::createHtmlElement('div', array(
        wfDocument::createHtmlElement('div', array(
          wfDocument::createHtmlElement('h4', $i18n->translateFromTheme($shop->get('end_text'))),
          wfDocument::createHtmlElement('p', $i18n->translateFromTheme($shop->get('end_description'), array('[end_time]' => $shop->get('end_time')))),
          wfDocument::createHtmlElement('p', $i18n->translateFromTheme($shop->get('next_description'), array('[next_days]' => $shop->get('next_days'), '[next_time]' => $shop->get('next_time'), '[next_hours]' => $shop->get('next_hours'))))
        ), array('class' => 'list-group-item'))  
      ), array('class' => 'list-group'));
      
      wfDocument::renderElement($element);
    }
  }
  /**
   * 
   */
  private static function getGoogleCalendar($google_calendar){
    wfPlugin::includeonce('google/calendar');
    $google = new PluginGoogleCalendar();
    $google->filename = $google_calendar;
    $google->init();
    return new PluginWfArray($google->calendar);
  }
}
