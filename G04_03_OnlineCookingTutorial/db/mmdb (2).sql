-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 05:11 PM
-- Server version: 10.4.32-MariaDB-log
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mmdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `logID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log`
--

INSERT INTO `log` (`logID`, `userID`, `action`, `details`, `created_at`) VALUES
(7, 5, 'Insert', 'User root created recipe ID 9 with title \'f\'', '2025-06-01 22:53:13'),
(8, 6, 'Update', 'User root updated recipe ID 3', '2025-06-03 11:22:16'),
(9, 6, 'Insert', 'User root created recipe ID 10 with title \'Hainan Chicken Rice\'', '2025-06-03 17:46:59'),
(10, 6, 'Insert', 'User root created recipe ID 11 with title \'Keto Mug Lasagna\'', '2025-06-04 12:08:20'),
(11, 6, 'Update', 'User root updated recipe ID 3', '2025-06-04 12:15:02'),
(12, 6, 'Insert', 'User root created recipe ID 12 with title \'nasi lemak\'', '2025-06-10 16:33:02');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `mediaID` int(11) NOT NULL,
  `media_type` enum('image','video') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `recipeID` int(11) DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `reviewID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`mediaID`, `media_type`, `file_path`, `metadata`, `uploaded_at`, `recipeID`, `userID`, `reviewID`) VALUES
(1, 'image', 'uploads/1748666206_sicilian_spaghetti.jpg', '{\"original_name\":\"6120770430995384894.jpg\",\"size\":4522,\"type\":\"image\\/jpeg\"}', '2025-06-01 06:17:15', NULL, NULL, NULL),
(2, 'image', 'uploads/keto-taco-casserole.jpg', NULL, '2025-06-02 19:30:43', NULL, NULL, NULL),
(3, 'image', 'uploads/Chinese-Honey-Chicken-2.jpg', NULL, '2025-06-02 19:34:41', NULL, NULL, NULL),
(4, 'image', 'uploads/vegan-red-lentil-curry.jpg', NULL, '2025-06-02 19:37:09', NULL, NULL, NULL),
(5, 'image', 'uploads/crispy-lentils.jpeg', NULL, '2025-06-02 19:45:01', NULL, NULL, NULL),
(6, 'image', 'uploads/media_683ec4a881fcf0.57590880.jpg', '{\"original_name\":\"keto_mug_lagsana.jpg\",\"size\":119598,\"type\":\"image\\/jpeg\"}', '2025-06-03 01:47:20', NULL, NULL, NULL),
(7, 'image', 'uploads/media_683fc703e01c99.41266873.jpg', '{\"original_name\":\"keto_mug_lagsana.jpg\",\"size\":119598,\"type\":\"image\\/jpeg\"}', '2025-06-03 20:09:39', NULL, NULL, NULL),
(8, 'video', 'uploads/media_6847edd0066f95.63899139.mp4', '{\"original_name\":\"Recording 2025-05-25 135346.mp4\",\"size\":13386843,\"type\":\"video\\/mp4\"}', '2025-06-10 08:33:20', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notificationID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notificationID`, `userID`, `message`, `created_at`) VALUES
(1, 6, 'Test message', '2025-06-01 18:03:42'),
(2, 6, 'Your review for recipe ID 1 has been posted.', '2025-06-01 18:10:15'),
(3, 6, 'Someone left a review on your recipe (ID: 1).', '2025-06-01 18:26:00'),
(4, 6, 'Someone left a review on your recipe (ID: 1).', '2025-06-01 18:26:13'),
(5, 6, 'Someone left a review on your recipe (ID: 1).', '2025-06-01 18:28:26'),
(6, 6, 'Someone left a review on your recipe (ID: 1).', '2025-06-01 18:32:12'),
(7, 6, 'Someone left a review on your recipe (ID: 1).', '2025-06-01 19:11:20'),
(8, 6, 'Someone left a review on your recipe (ID: 8).', '2025-06-01 19:16:41'),
(9, 6, 'Someone left a review on your recipe (ID: 8).', '2025-06-01 19:17:28'),
(10, 6, 'Someone left a review on your recipe (ID: 8).', '2025-06-02 11:58:45'),
(11, 6, 'Someone left a review on your recipe (ID: 8).', '2025-06-02 11:59:12'),
(12, 6, 'Someone left a review on your recipe (ID: 8).', '2025-06-02 12:06:43'),
(13, 6, 'Someone left a review on your recipe (ID: 8).', '2025-06-02 12:07:23'),
(14, 6, 'Someone left a review on your recipe (ID: 8).', '2025-06-02 12:09:06'),
(15, 5, 'Someone left a review on your recipe (ID: 1).', '2025-06-03 10:59:04'),
(16, 5, 'Someone left a review on your recipe (ID: 1).', '2025-06-03 17:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `recipe`
--

CREATE TABLE `recipe` (
  `recipeID` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `ingredient` text DEFAULT NULL,
  `dietary_type` varchar(50) DEFAULT NULL,
  `cuisine_type` varchar(50) DEFAULT NULL,
  `step` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `userID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe`
--

INSERT INTO `recipe` (`recipeID`, `title`, `ingredient`, `dietary_type`, `cuisine_type`, `step`, `created_at`, `updated_at`, `userID`) VALUES
(1, 'Sicilian spaghetti', '• 230g - 8oz Spaghetti\r\n• 4 tbsp extra virgin olive oil\r\n• 2-3 cloves of garlic\r\n• ½ tsp dried hot chili flakes\r\n• 1 tbsp tomato paste\r\n• ¾ cup dry white wine\r\n• 1oz – 30g white anchovy fillets (approx. 8)\r\n• 2oz – 60g Mixed olives (pitted)\r\n• 2 tbsp capers (drained or desalted)\r\n• 8oz – 230g washed cherry tomatoes, mixed variety\r\n• Hand full of fresh parsley leaves\r\n• 1 cup grated Parmesan Cheese', 'Vegetarian', 'Italian', '1. Place a large pot of water for your pasta on high heat. Once boiling add enough salt. (If you salt correctly you won’t require further seasoning). Add the spaghetti once boiling and cook al dente. The sauce should be ready by then.\r\n2. While the pasta water is heating, get your ingredients ready. Peel and finely slice your garlic cloves. Pit the olives and cut in half. Cut the cherry tomatoes in half. Grate the Parmesan cheese very finely. Chop the parsley quite coarsely. Set all ingredients aside.\r\n3. To a large frying pan on medium heat, add 3 tbsp of olive oil. Add the garlic slices and chili flakes. Sweat the garlic 30 seconds or so, to soften not brown. Add the tomato paste and let it fry in the oil as you mix ingredients. Deglaze with white wine. Add the white anchovies, the olives and capers. Bring to a simmer and stew 30 seconds. Add a couple ladles of hot pasta water and the cut cherry tomatoes. Leave to cook approx. 3 minutes until soft but still firm.\r\n4. Drain pasta and add to sauce. Add the chopped parley and mix well over high heat. Turn off heat, add half the cheese and mix again, add the rest of the cheese, and mix some more. If it looks too dry, add a little pasta water. Ready to serve.\r\n5. Drizzle a little more olive oil on top and sprinkle grated Parmesan cheese.', '2025-05-28 16:07:42', '2025-05-28 19:22:14', 5),
(2, 'Keto Taco Casserole', '• Olive oil\r\n• Ground beef\r\n• Taco seasoning\r\n• Salsa\r\n• Shredded cheese\r\n', 'Keto', 'Mexican', '1. Start by adding the olive oil into a non-stick pan and placing it over medium heat. \r\n2. Once hot, add the beef, break it apart, and cook it until no longer brown. Add the taco seasoning and water and let everything simmer together for 5 minutes. Stir through the salsa at the end.\r\n3. Next, slice your tortillas in half and place half of them at the base of a greased 9 x 13-inch baking dish. Top with half the seasoned beef mixture and half the shredded cheese. \r\n4. Add the remaining tortilla halves and beef mixture, and cover everything with the remaining cheese.\r\n5. Now, cover the baking dish with tin foil and bake for 15-20 minutes, until the cheese has melted. \r\n6. Let the casserole sit for 5 minutes before serving. If desired, add some fun toppings like sour cream and sliced avocado.', '2025-05-28 17:31:06', '2025-05-28 19:45:56', 6),
(3, 'Gluten Free Chinese Honey Chicken', '• Honey\r\n• Gluten free soy sauce\r\n• Apple cider vinegar\r\n• Honey\r\n• Gluten free soy sauce\r\n• Sesame oil\r\n• Crushed red pepper flakes\r\n• Cornstarch \r\n• Brown sugar\r\n• Egg\r\n• Chicken\r\n• Olive Oil\r\n• Thai chili pepper \r\n• Garlic\r\n• Green onions', 'Gluten-Free', 'Chinese', '1. Prepare the sauce. Combine honey, gluten free soy sauce, apple cider vinegar, sesame oil and crushed red pepper flakes in a small bowl and set aside.\r\n2. Prepare the batter station for the chicken. Add one whisked egg to a shallow bowl or large rimmed plate. Combine cornstarch and brown sugar in another shallow bowl. Coat each piece of chicken in the whisked egg. Then move the chicken pieces to the cornstarch mixture, making sure any excess liquid from the egg drips off before you add the chicken to the cornstarch. Always use one hand for the wet mixture (the egg) and another hand for the dry mixture (the cornstarch). This will prevent clumping in the cornstarch mixture.\r\n3. Cook the chicken. Heat olive oil in a large skillet, or wok, on the stove over medium high heat. Once the oil is hot, add the battered chicken, along with the minced garlic and chili pepper.\r\n4. Cook until browned. After 3-5 minutes, the chicken should be golden brown on all sides.\r\n5. Add the sauce. Once the chicken is browned on the outside, pour the honey sauce over the chicken in the skillet. Cook for 2-3 minutes.\r\n6. Simmer with the sauce. Reduce the heat on the stove to low, cover the skillet and simmer the chicken in the honey sauce for 6-8 minutes. Remove the lid and serve the chicken immediately topped with diced green onions and sesame seeds, if you’d like.', '2025-05-28 17:47:29', '2025-05-28 19:52:06', 6),
(4, 'Red Lentil Curry', '• red lentils\r\n• cumin\r\n• garam masala\r\n• coriander\r\n• lemon\r\n• turmeric\r\n• ginger\r\n• garlic\r\n• curry powder\r\n• indian red chile powder\r\n• vegetable broth\r\n• serrano peppers\r\n• coconut milk\r\n• cilantro\r\n• almond butter\r\n• crushed tomatoes', 'Vegan', 'Indian', '1. Sauté the aromatics. Melt the coconut oil in a large, deep skillet over medium-high heat. Once it’s hot and shimmering, add the garlic, ginger, fresh turmeric, and Serrano pepper. Sauté until they’re soft and fragrant.\r\n2. Add the spices and let them toast for up to 1 minute to bring out their hidden flavors.\r\n3. Deglaze the skillet with the vegetable broth, scraping up any browned bits stuck to the bottom. Pour in the lentils and crushed tomatoes next. Stir to combine.\r\n4. Turn down the heat and cover the pan with a lid. Let the curry simmer until the lentils are mostly softened.\r\n5. Stir in the coconut milk, almond butter, salt, and pepper. Taste and adjust the seasonings to your liking.\r\n6. Continue cooking until the curry is thick and creamy.\r\n7. To finish, stir in the lemon juice and cilantro, then turn off the heat. Serve the curry with rice and Indian flatbread, and enjoy!', '2025-05-28 20:29:29', '2025-05-28 20:29:29', 6),
(5, 'Crispy Indian-ish Lentils with Rice & Yogurt', '• 3/4 cup (150g) brown, green, or French green lentils\r\n• Kosher salt and freshly ground black pepper\r\n• 1 cup (190g) uncooked long-grain white rice\r\n• 1 teaspoon black or brown mustard seeds\r\n• 1 teaspoon cumin seeds\r\n• 1 teaspoon coriander seeds\r\n• 3 tablespoons neutral-flavored, high-heat oil of choice\r\n• 1 medium shallot, very thinly sliced into rounds (1/8-inch or 3mm slices)\r\n• 4 garlic cloves, thinly sliced\r\n• 1.5 inch (3.75 cm) piece fresh ginger, peeled and sliced into matchsticks\r\n• 1 small serrano pepper, thinly sliced into rounds\r\n• 1/2 teaspoon ground turmeric\r\n• Flaky sea salt\r\n• 1/2 cup (115g) good-quality plain coconut yogurt\r\n• 2 to 3 teaspoons freshly squeezed lemon juice\r\n• 1/4 teaspoon organic cane sugar\r\n• 1/2 teaspoon ground cumin\r\n• Kosher salt and freshly ground black pepper\r\n• 3/4 cup (12g) fresh cilantro leaves and tender stems, chopped\r\n• 1 tablespoon freshly squeezed lemon juice\r\n', 'Gluten-Free', 'Indian', '1. Bring a medium saucepan of water to a boil. Add the lentils and 2 teaspoons kosher salt (1 tsp sea salt). Reduce the heat and simmer until the lentils are al dente (tender but with a bite), 10 to 12 minutes (17 to 20 minutes for French green lentils); they should not be soft.\r\nDrain and shake to get rid of excess water.\r\n2. Transfer the lentils to a large dish towel to dry. I like to gently run my hands through the lentils so they dry more quickly.\r\n3. Meanwhile, cook the rice using your preferred method, or get out your leftover cooked rice.\r\n4. Lightly crush the mustard, cumin, and coriander seeds in a mortar with a pestle or add to a spice grinder and pulse just once or twice.\r\n5. Place a fine-mesh strainer over a small or medium bowl and line a large plate with paper towels. Heat the oil in a medium or large skillet over medium heat.\r\nOnce shimmering, add the shallots along with a pinch of salt. Cook, stirring occasionally and separating the shallot slices, until the edges are just turning golden, 3 to 4 minutes.\r\nAdd the crushed spices, the garlic, ginger, serrano, and turmeric and cook, stirring frequently, until the garlic is golden and very aromatic, 2 to 3 minutes.\r\nRemove from the heat and pour the mixture into the strainer; you’ll use the oil that drains into the bowl to broil the lentils. Transfer the aromatics to the towel-lined plate and sprinkle with a pinch or two of flaky salt.\r\n6. While the lentils dry, make the yogurt sauce. (This can also be made 1 to 2 days in advance.) In a small or medium bowl, mix together the yogurt, lemon juice, sugar, and cumin. Season to taste with kosher salt and black pepper.\r\n7. Arrange a rack on the second shelf below the broiler. Preheat the broiler on high for about 5 minutes.\r\n8. Transfer the lentils to a rimmed sheet pan, toss with the reserved oil from step 5, sprinkle with a pinch or two of kosher salt and black pepper, and shake the pan back and forth to spread the lentils out into an even layer, using your hands to smooth out any clumps.\r\n9. Broil the lentils for 4 minutes. Toss with a spatula or shake the pan back and forth to evenly redistribute them. Broil for 2 minutes and shake the pan again.\r\nIf they’re nicely crispy, they’re done. If they’re starting to crisp up, broil for 1 minute, then check and broil in 1-minute increments as needed. If they haven’t started to crisp up yet, broil for 2 minutes, then check.\r\n10. Transfer the crispy lentils to a serving bowl and toss with the reserved fried aromatics, cilantro, and lemon juice. Season to taste with flaky salt. Serve the lentils on top of rice and drizzle yogurt sauce on top.', '2025-05-28 20:35:20', '2025-05-28 20:35:20', 6),
(11, 'Keto Mug Lasagna', '• ⅓ medium zucchini, ~65g\r\n• 3 tablespoons Rao’s marinara\r\n• 2 tablespoons whole milk ricotta cheese\r\n• 3 ounces whole milk mozzarella cheese', 'Keto', 'Italian', '1. Slice the zucchini into paper thin rounds. You can use a really sharp knife, or a mandolin.\r\n2. In the bottom of your dish add a tablespoon of the marinara.\r\n3. Layer on some of the zucchini.\r\n4. Carefully spread out 1 tablespoon of ricotta.\r\n5. Add another tablespoon of marinara.\r\n6. Layer on the second layer of zucchini, another tablespoon of ricotta, any leftover zucchini, and then the last tablespoon of marinara.\r\n7. Top with the mozzarella.\r\n8. Microwave for 3-4 minutes, depending on the strength of your microwave. You can sprinkle on a little oregano or Parmesan cheese if you like.\r\n9. ', '2025-06-04 12:08:20', '2025-06-04 12:08:20', 6),
(12, 'nasi lemak', '• nasi\r\n• ayam', 'Gluten-Free', 'Indian', '1. masak', '2025-06-10 16:33:02', '2025-06-10 16:33:02', 6);

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `reviewID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `recipeID` int(11) NOT NULL,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `media_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`reviewID`, `userID`, `recipeID`, `rating`, `review_text`, `created_at`, `media_path`) VALUES
(18, 5, 2, 5, 'perfect recipe', '2025-05-30 17:06:45', ''),
(19, 5, 1, 5, 'delicious', '2025-05-31 17:20:35', ''),
(30, 5, 3, 4, 'uuu', '2025-05-31 19:11:20', NULL),
(33, 5, 4, 5, 'a good recipe', '2025-06-01 11:58:45', NULL),
(37, 5, 5, 5, 'easy to make', '2025-06-01 12:09:06', 'uploads/1748837346_1748836725_media_683abc2ee34e27.11308773.jpeg'),
(39, 6, 1, 5, 'Delicious', '2025-06-03 01:44:39', 'uploads/1748943879_keto_mug_lagsana.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('chef','student') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `username`, `password_hash`, `email`, `full_name`, `role`, `created_at`, `profile_pic`) VALUES
(5, 'Lam', '$2y$10$9MeIV/vKI/RcwVBq12ru1ub2SVddfe0ArsEkgwXrBXTfmGGSs7H9C', 'lam@gmail.com', 'Vanness Lam', 'student', '2025-06-01 22:21:29', NULL),
(6, 'Medina', '$2y$10$3mltuPsU1nrxn8sY4i67p./8r99KIn3m7BfwGM2zLkLSUB4KcOdHi', 'Medina1208@gmail.com', 'Medina Sofea', 'chef', '2025-06-03 10:22:35', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`logID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`mediaID`),
  ADD KEY `fk_media_recipe` (`recipeID`),
  ADD KEY `fk_media_reviewID` (`userID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notificationID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `recipe`
--
ALTER TABLE `recipe`
  ADD PRIMARY KEY (`recipeID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`reviewID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `recipeID` (`recipeID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `logID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `mediaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `recipe`
--
ALTER TABLE `recipe`
  MODIFY `recipeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `reviewID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `log_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `fk_media_recipe` FOREIGN KEY (`recipeID`) REFERENCES `recipe` (`recipeID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_media_reviewID` FOREIGN KEY (`userID`) REFERENCES `review` (`reviewID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_media_userID` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `recipe`
--
ALTER TABLE `recipe`
  ADD CONSTRAINT `recipe_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`recipeID`) REFERENCES `recipe` (`recipeID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
