;; Gauntlet MUD - Utility functions
;; Copyright (C) 2017-2025 Pekka Laiho
;; License: AGPL 3.0 (see LICENSE)

;; ----------------
;; helper functions
;; ----------------

;; move in random direction
(defn move-rand (ch)
  (let (dir (rand-from-seq (keys (get-exits (get-room ch) ch))))
    (when dir
      (move ch dir))))

;; try to equip item if empty slot is found
;; return slot if successful
(defn try-equip (ch obj)
  (let (slot (find-empty-slot ch obj))
    (when slot
      (do (wear ch obj slot) slot))))

(defn find-monster-by-id (room id)
  (first (filter (fn (a) (and (is-monster? a) (= (get-id a) id))) (get-living room))))

;; ----------------------
;; monster update actions
;; ----------------------

;; execute a random action from the list
(defn mon-act (ch actions)
  (let (act (rand-from-seq actions))
    (apply (if (seq? act) (first act) act) ch (if (seq? act) (second act) []))))

;; attack players
;; return victim if successful
(defn mon-aggr-plr (ch)
  (unless (has-flag? (get-room ch) "peaceful")
    (let (victim (rand-from-seq (find-living ch "player")))
      (when victim
        (do (attack ch victim) victim)))))

;; attack given ids
;; return victim if successful
(defn mon-aggr-ids (ch ids)
  (let (victim (rand-from-seq (find-living ch "monster" ids)))
    (when victim
      (do (attack ch victim) victim))))

;; attack vermin
;; return victim if successful
(defn mon-aggr-vermin (ch)
  (let (victim (rand-from-seq (find-living ch "monster" "vermin")))
    (when victim
      (do
       (when (is-male? ch)
         (social ch (rand-from-seq ["belch" "charge" "curse" "knuckle"]) victim))
       (when (is-female? ch)
         (social ch (rand-from-seq ["cringe" "disgust" "gasp" "goose"]) victim))
        (attack ch victim false)
        victim))))

;; assist victim in distress
;; return victim if successful
(defn mon-assist (ch)
  (let (victim (rand-from-seq (find-living ch "assist")))
    (when victim
      (do (assist ch victim) victim))))

;; assist given ids
;; return victim if successful
(defn mon-assist-ids (ch ids)
  (let (victim (rand-from-seq (find-living ch "monster" "fighting" ids)))
    (when victim
      (do (assist ch victim) victim))))

;; equip an item from inventory
;; return item if successful
(defn mon-equip (ch)
  (let (obj (rand-from-seq (find-items ch (get-items ch) "equipment")))
    (when (and obj (try-equip ch obj)) obj)))

;; pick up useful items from ground
;; return item if successful
(defn mon-pickup (ch)
  (let (obj (rand-from-seq (find-items ch (get-items (get-room ch)) "useful")))
    (when obj
      (do (pickup ch obj) obj))))

;; loot monster corpses
;; return item if successful
(defn mon-loot (ch)
  (let (corpse (rand-from-seq (find-items ch (get-items (get-room ch)) "mcorpse")))
    (when corpse
      (let (obj (rand-from-seq (find-items ch (get-items corpse) "useful")))
        (when obj
          (do (get-from-container ch obj corpse) obj))))))

;; ---------------------
;; monster fight actions
;; ---------------------

;; flee from combat if health under 33%
;; return direction if flee is successful
;; return true if failed attempt was made
(defn mon-flee (ch)
  (let (ratio (/ (get-health ch) (get-max-health ch)))
    (when (and (percent 50) (<= ratio 0.33))
      (let (dir (flee ch))
        (if dir dir true)))))

;; -----------------------------------
;; monster memory (remember attackers)
;; -----------------------------------

(defn mon-memory-init (ch)
  (set-my-env ch "memory" (hash)))

;; this function must return falsy value
;; so that default attack is executed
(defn mon-memory-add (ch)
  (do (set! (get-my-env ch "memory") (get-uniq-name (get-target ch)) 1) null))

;; attack previous attacker
;; return victim if successful
(defn mon-aggr-memory (ch)
  (let (memory (get-my-env ch "memory")
        victim (rand-from-seq (filter (fn (a) (key? memory (get-uniq-name a))) (find-living ch))))
    (when victim
      (do
       (say ch "Hey! You are the fiend that attacked me!")
       (attack ch victim false)
       victim))))
