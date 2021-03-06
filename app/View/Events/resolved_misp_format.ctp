<div class="index">
    <h2><?php echo h($title); ?></h2>
    <?php
        $url = '/events/handleModuleResults/' . $event['Event']['id'];
        echo $this->Form->create('Event', array('url' => $url, 'class' => 'mainForm'));
        $formSettings = array(
            'type' => 'hidden',
            'value' => json_encode($event, true)
        );
        echo $this->Form->input('data', $formSettings);
        echo $this->Form->end();
        $scope = !empty($proposals) ? 'proposals of' : '';
        $objects_array = array();
        if (isset($event['Attribute'])) {
            array_push($objects_array, 'attributes');
        }
        if (isset($event['Object'])) {
            array_push($objects_array, 'objects');
        }
        if (isset($resultArray) && !in_array('attributes', $objects_array, true) && in_array('objects', $objects_array, true)) {
            $scope .= __('simplified attributes and');
        }
        $scope .= !empty($objects_array) ? join(' and ', $objects_array) : 'simplified attributes';
        if (!isset($importComment)) {
            $importComment = $attributeValue . ': Enriched via the ' . $module . ' module';
        }
    ?>
    <p><?php echo __('Below you can see the %s that are to be created, from the results of the enrichment module.', $scope);?></p>
    <?php
        $attributeFields = array('category', 'type', 'value', 'uuid');
        if (isset($event['Object']) && !empty($event['Object'])) {
    ?>
    <div class='MISPObjects' style="margin-bottom:40px;">
      <h3><?php echo __('Objects'); ?></h3>
      <?php
            foreach ($event['Object'] as $o => $object) {
      ?>
      <div class='MISPObject'>
        <table style="width:100%;">
          <tbody>
            <tr>
              <td class="bold"><?php echo __('Name');?></td>
              <td class='ObjectName'><?php echo h($object['name']); ?></td>
            </tr>
            <tr>
              <td class="bold"><?php echo __('UUID');?></td>
              <td class='ObjectUUID'><?php echo h($object['uuid']); ?></td>
            </tr>
          </tbody>
        </table>
        <?php if (isset($object['ObjectReference']) && !empty($object['ObjectReference'])) { ?>
        <tr>
          <td class="bold"><?php echo __('References:'); ?></td>
        </tr>
        <table class="ObjectReferences" style="margin-bottom:0px;text-align:left;width:100%;">
          <thead>
            <th><?php echo __('Referenced name/type'); ?></th>
            <th><?php echo __('Referenced uuid'); ?></th>
            <th><?php echo __('Relationship'); ?></th>
          </thead>
          <tbody>
            <?php
                    foreach ($object['ObjectReference'] as $reference) {
                        echo '<tr class="ObjectReference">';
                        $referenced_uuid = $reference['referenced_uuid'];
                        foreach ($event['Object'] as $object_reference) {
                            if ($referenced_uuid === $object_reference['uuid']) {
                                $name = $object_reference['name'];
                                break;
                            }
                        }
                        if (!isset($name)) {
                            foreach ($event['Attribute'] as $attribute_reference) {
                                if ($referenced_uuid === $attribute_reference['uuid']) {
                                    $name = $attribute_reference['type'];
                                    break;
                                }
                            }
                            if (!isset($name)) {
                                $name = '';
                            }
                        }
                        echo '<td class="ReferencedName">' . h($name) . '</td>';
                        unset($name);
                        echo '<td class="ReferencedUUID">' . h($referenced_uuid) . '</td>';
                        echo '<td class="Relationship">' . h($reference['relationship_type']) . '</td>';
                        echo '</tr>';
                    }
            ?>
          </tbody>
        </table>
        <?php } ?>
        <table class="ObjectAttributes table table-condensed table-striped" style="text-align:left;margin-bottom:20px;">
          <thead>
            <th><?php echo __('Attribute');?></th>
            <th><?php echo __('Category');?></th>
            <th><?php echo __('Type');?></th>
            <th><?php echo __('Value');?></th>
            <th><?php echo __('UUID');?></th>
            <th><?php echo __('To IDS');?></th>
            <th><?php echo __('Comment');?></th>
            <th><?php echo __('Distribution');?></th>
          </thead>
          <tbody>
            <?php
                if (!empty($object['Attribute'])) {
                    foreach ($object['Attribute'] as $a => $attribute) {
                        echo '<tr class="ObjectAttribute">';
                        echo '<td class="ObjectRelation">' . h($attribute['object_relation']) . '</td>';
                        if ($attribute['distribution'] != 4) {
                            $attribute['distribution'] = $distributions[$attribute['distribution']];
                        } else {
                            $attribute['distribution'] = $sgs[$attribute['sharing_group_id']];
                        }
                        foreach ($attributeFields as $field) {
                            echo '<td class="' . ucfirst($field) . '">' . (isset($attribute[$field]) ? h($attribute[$field]) : '') . '</td>';
                        }
            ?>
            <td class="short" style="width:40px;text-align:center;">
              <input type="checkbox" id="<?php echo 'Object' . $o . 'Attribute' . $a . 'To_ids'; ?>" <?php if (isset($attribute['to_ids']) && $attribute['to_ids']) echo 'checked'; ?> class="idsCheckbox"/>
            </td>
            <td class="short">
              <input type="text" class="freetextCommentField" id="<?php echo 'Object' . $o . 'Attribute' . $a . 'Comment'; ?>" style="padding:0px;height:20px;margin-bottom:0px;" placeholder="<?php echo h($importComment); ?>" <?php if (isset($attribute['comment']) && $attribute['comment'] !== false) echo 'value="' . h($attribute['comment']) . '"';?>/>
            </td>
            <td class="short" style="width:40px;text-align:center;">
              <select id="<?php echo 'Object' . $o . 'Attribute' . $a . 'Distribution'; ?>" class='distributionToggle' style='padding:0px;height:20px;margin-bottom:0px;'>
                <?php
                        foreach ($distributions as $distKey => $distValue) {
                            echo '<option value="' . $distKey . '" ' . ($distValue == $attribute['distribution'] ? 'selected="selected"' : '') . '>' . $distValue . '</option>';
                        }
                ?>
              </select>
              <div style="display:none;">
                <select id="<?php echo 'Object' . $o . 'Attribute' . $a . 'SHaringGroupId'; ?>" class='sgToggle' style='padding:0px;height:20px;margin-top:3px;margin-bottom:0px;'>
                  <?php
                        foreach ($sgs as $sgKey => $sgValue) {
                            echo '<option value="' . h($sgKey) . '">' . h($sgValue) . '</option>';
                        }
                  ?>
                </select>
              </div>
            </td>
            <?php
                        echo '</tr>';
                    }
                }
            ?>
          </tbody>
        </table>
      </div>
      <?php
            }
        }
      ?>
    </div>
    <?php
        if (isset($event['Attribute']) && !empty($event['Attribute'])) {
    ?>
    <div class='MISPAttributes'>
      <h3><?php echo __('Attributes'); ?></h3>
      <table class="table table-condensed table-stripped">
        <thead>
          <th><?php echo __('Category');?></th>
          <th><?php echo __('Type');?></th>
          <th><?php echo __('Value');?></th>
          <th><?php echo __('UUID');?></th>
          <th><?php echo __('To IDS');?></th>
          <th><?php echo __('Comment');?></th>
          <th><?php echo __('Distribution');?></th>
        </thead>
        <tbody>
          <?php
            foreach ($event['Attribute'] as $a => $attribute) {
                echo '<tr class="MISPAttribute">';
                if ($attribute['distribution'] != 4) {
                    $attribute['distribution'] = $distributions[$attribute['distribution']];
                } else {
                    $attribute['distribution'] = $sgs[$attribute['sharing_group_id']];
                }
                foreach ($attributeFields as $field) {
                    echo '<td class="' . ucfirst($field) . '">' . (isset($attribute[$field]) ? h($attribute[$field]) : '') . '</td>';
                }
          ?>
          <td class="short" style="width:40px;text-align:center;">
            <input type="checkbox" id="<?php echo 'Attribute' . $a . 'To_ids'; ?>" <?php if (isset($attribute['to_ids']) && $attribute['to_ids']) echo 'checked'; ?> class='idsCheckbox'/>
          </td>
          <td class="short">
            <input type="text" class="freetextCommentField" id="<?php echo 'Attribute' . $a . 'Comment'; ?>" style="padding:0px;height:20px;margin-bottom:0px;" placeholder="<?php echo h($importComment); ?>" <?php if (isset($attribute['comment']) && $attribute['comment'] !== false) echo 'value="' . h($attribute['comment']) . '"';?>/>
          </td>
          <td class="short" style="width:40px;text-align:center;">
            <select id="<?php echo 'Attribute' . $a . 'Distribution'; ?>" class='distributionToggle' style='padding:0px;height:20px;margin-bottom:0px;'>
            <?php
                foreach ($distributions as $distKey => $distValue) {
                    echo '<option value="' . $distKey . '" ' . ($distValue == $attribute['distribution'] ? 'selected="selected"' : '') . '>' . $distValue . '</option>';
                }
            ?>
            </select>
            <div style="display:none;">
              <select id="<?php echo 'Attribute' . $a . 'SharingGroupId'; ?>" class='sgToggle' style='padding:0px;height:20px;margin-top:3px;margin-bottom:0px;'>
                <?php
                foreach ($sgs as $sgKey => $sgValue) {
                    echo '<option value="' . h($sgKey) . '">' . h($sgValue) . '</option>';
                }
                ?>
              </select>
            </div>
          </td>
          <?php
                echo '</tr>';
            }
        }
          ?>
        </tbody>
      </table>
    </div>
    <button class="btn btn-primary" style="float:left;" onClick="moduleResultsSubmit('<?php echo h($event['Event']['id']); ?>');"><?php echo __('Submit oui'); ?></button>
</div>
<?php
    if (!isset($menuItem)) {
        $menuItem = 'freetextResults';
    }
    echo $this->element('/genericElements/SideMenu/side_menu', array('menuList' => 'event', 'menuItem' => $menuItem));
?>
<script type="text/javascript">
    $(document).ready(function() {
      $('.distributionToggle').change(function() {
          if ($(this).val() == 4) {
              $(this).next().show();
          } else {
              $(this).next().hide();
          }
      });
    });
</script>
